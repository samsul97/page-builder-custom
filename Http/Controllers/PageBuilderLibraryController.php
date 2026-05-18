<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Models\PageBuilderReusableBlock;
use App\Models\SiteSetting;
use Modules\PageBuilder\Support\PageBuilderBlockTypeRegistry;
use Modules\PageBuilder\Support\PageBuilderLibraryCatalog;
use Modules\PageBuilder\Support\PageBuilderPresetCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageBuilderLibraryController extends Controller
{
    public function __construct(
        protected PageBuilderLibraryCatalog $catalog,
        protected PageBuilderPresetCatalog $presetCatalog,
        protected PageBuilderBlockTypeRegistry $blockTypeRegistry,
    ) {
    }

    public function index(): View
    {
        $presetFilter = request()->query('preset');
        $items = $this->catalog->all();

        if (filled($presetFilter)) {
            $items = $items
                ->filter(fn (array $item) => data_get($item, 'related_preset_key') === $presetFilter)
                ->values();
        }

        return view('pagebuilder::library.index', [
            'items' => $items,
            'groupedItems' => $items->groupBy('category')->sortKeys(),
            'presetFilter' => $presetFilter,
            'activations' => $this->libraryActivations(),
            'blockTypes' => $this->blockTypeRegistry->all(),
            'groupedBlockTypes' => $this->blockTypeRegistry->all()->groupBy('category')->sortKeys(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $existingKeys = $this->catalog->all()->pluck('key')->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'key' => ['nullable', 'string', 'max:190', Rule::notIn($existingKeys)],
            'type' => ['required', 'string', Rule::in(['theme', 'plugin'])],
            'category' => ['required', 'string', Rule::in(['theme', 'block', 'system'])],
            'status' => ['required', 'string', Rule::in(['enabled', 'disabled', 'planned'])],
            'description' => ['required', 'string'],
            'related_preset_key' => ['nullable', 'string', 'max:190'],
        ]);

        $customItems = $this->catalog->customItems()->values()->all();
        $key = $validated['key'] !== ''
            ? Str::slug($validated['key'])
            : Str::slug($validated['category'] . '-' . $validated['name']);

        if (in_array($key, $existingKeys, true)) {
            return back()
                ->withErrors(['key' => 'The generated library key already exists. Please choose a different key.'])
                ->withInput();
        }

        $customItems[] = [
            'key' => $key,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'source' => 'registered-internal',
            'description' => $validated['description'],
            'related_preset_key' => $validated['related_preset_key'] ?: null,
            'is_custom' => true,
        ];

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_library_custom_items'],
            ['value' => json_encode(array_values($customItems), JSON_UNESCAPED_SLASHES)]
        );

        site_setting_forget_cache();

        flash()->success('Library asset registered successfully.');

        return redirect()->route('page-builder.plugins-theme.index');
    }

    public function toggle(string $item): RedirectResponse
    {
        $libraryItem = $this->catalog->findOrFail($item);
        $states = $this->catalog->stateOverrides();
        $currentStatus = data_get($libraryItem, 'status', 'planned');
        $nextStatus = $currentStatus === 'enabled' ? 'disabled' : 'enabled';

        $states[data_get($libraryItem, 'key')] = $nextStatus;

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_library_states'],
            ['value' => json_encode($states, JSON_UNESCAPED_SLASHES)]
        );

        site_setting_forget_cache();

        flash()->success("Library item {$nextStatus}: " . data_get($libraryItem, 'name'));

        return redirect()->route('page-builder.plugins-theme.index');
    }

    public function importManifest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'manifest_json' => ['nullable', 'string'],
            'manifest_file' => ['nullable', 'file', 'max:1024'],
        ]);

        $manifestJson = trim((string) data_get($validated, 'manifest_json'));

        if ($request->hasFile('manifest_file')) {
            $manifestJson = (string) file_get_contents($request->file('manifest_file')->getRealPath());
        }

        if ($manifestJson === '') {
            return back()
                ->withErrors(['manifest_json' => 'Paste manifest JSON or upload a manifest file.'])
                ->withInput();
        }

        $manifest = json_decode($manifestJson, true);

        if (! is_array($manifest)) {
            return back()
                ->withErrors(['manifest_json' => 'Manifest must be valid JSON.'])
                ->withInput();
        }

        $items = data_get($manifest, 'items', []);

        if (! is_array($items) || count($items) === 0) {
            return back()
                ->withErrors(['manifest_json' => 'Manifest must include at least one item in the items array.'])
                ->withInput();
        }

        $existingKeys = $this->catalog->all()->pluck('key')->all();
        $existingPresetKeys = $this->presetCatalog->all()->pluck('key')->all();
        $customItems = $this->catalog->customItems()->values()->all();
        $importedItems = [];
        $customPresets = $this->presetCatalog->customPresets()->values()->all();
        $importedPresets = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                return back()
                    ->withErrors(['manifest_json' => "Manifest item #{$index} must be an object."])
                    ->withInput();
            }

            $key = Str::slug((string) data_get($item, 'key'));
            $name = trim((string) data_get($item, 'name'));
            $type = trim((string) data_get($item, 'type', 'plugin'));
            $category = trim((string) data_get($item, 'category', 'block'));
            $status = trim((string) data_get($item, 'status', 'planned'));
            $description = trim((string) data_get($item, 'description'));

            if ($key === '' || $name === '' || $description === '') {
                return back()
                    ->withErrors(['manifest_json' => "Manifest item #{$index} requires key, name, and description."])
                    ->withInput();
            }

            if (in_array($key, $existingKeys, true) || in_array($key, array_column($importedItems, 'key'), true)) {
                return back()
                    ->withErrors(['manifest_json' => "Manifest item key already exists: {$key}."])
                    ->withInput();
            }

            if (! in_array($type, ['theme', 'plugin'], true)) {
                return back()
                    ->withErrors(['manifest_json' => "Manifest item {$key} has invalid type. Use theme or plugin."])
                    ->withInput();
            }

            if (! in_array($category, ['theme', 'block', 'system'], true)) {
                return back()
                    ->withErrors(['manifest_json' => "Manifest item {$key} has invalid category. Use theme, block, or system."])
                    ->withInput();
            }

            if (! in_array($status, ['enabled', 'disabled', 'planned'], true)) {
                return back()
                    ->withErrors(['manifest_json' => "Manifest item {$key} has invalid status. Use enabled, disabled, or planned."])
                    ->withInput();
            }

            $importedItems[] = [
                'key' => $key,
                'name' => $name,
                'type' => $type,
                'category' => $category,
                'status' => $status,
                'source' => 'imported-manifest',
                'description' => $description,
                'related_preset_key' => trim((string) data_get($item, 'related_preset_key')) ?: null,
                'package' => trim((string) data_get($item, 'package')) ?: null,
                'version' => trim((string) data_get($item, 'version')) ?: null,
                'activation' => $this->activationFromManifestItem($item, $key),
                'is_custom' => true,
            ];

            if ($type === 'theme' && $category === 'theme') {
                $preset = $this->presetFromManifestItem($item, $key, $name, $description);
                $presetKey = (string) data_get($preset, 'key');

                if (in_array($presetKey, $existingPresetKeys, true) || in_array($presetKey, array_column($importedPresets, 'key'), true)) {
                    return back()
                        ->withErrors(['manifest_json' => "Theme manifest preset key already exists: {$presetKey}."])
                        ->withInput();
                }

                $importedPresets[] = $preset;
            }
        }

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_library_custom_items'],
            ['value' => json_encode(array_values([...$customItems, ...$importedItems]), JSON_UNESCAPED_SLASHES)]
        );

        if (count($importedPresets) > 0) {
            SiteSetting::query()->updateOrCreate(
                ['key' => 'page_builder_custom_presets'],
                ['value' => json_encode(array_values([...$customPresets, ...$importedPresets]), JSON_UNESCAPED_SLASHES)]
            );
        }

        site_setting_forget_cache();

        flash()->success(count($importedItems) . ' library asset(s) imported from manifest. Created ' . count($importedPresets) . ' preset blueprint(s).');

        return redirect()->route('page-builder.plugins-theme.index');
    }

    public function toggleBlockType(string $type): RedirectResponse
    {
        $blockType = $this->blockTypeRegistry->toggle($type);

        flash()->success('Block type ' . data_get($blockType, 'status') . ': ' . data_get($blockType, 'label'));

        return redirect()->route('page-builder.plugins-theme.index');
    }

    public function activate(string $item): RedirectResponse
    {
        $libraryItem = $this->catalog->findOrFail($item);

        if (data_get($libraryItem, 'status') !== 'enabled') {
            return back()->withErrors([
                'activation' => 'Enable this library asset before activating its contract.',
            ]);
        }

        $activation = data_get($libraryItem, 'activation', []);
        $contract = data_get($activation, 'contract');

        if ($contract !== 'block_pack') {
            return back()->withErrors([
                'activation' => 'This library asset does not have an activatable block pack contract.',
            ]);
        }

        $created = 0;
        $skipped = 0;

        foreach (data_get($activation, 'reusable_blocks', []) as $blockRecipe) {
            if (! is_array($blockRecipe)) {
                $skipped++;

                continue;
            }

            $slug = Str::slug((string) data_get($blockRecipe, 'slug', data_get($blockRecipe, 'key')));
            $name = trim((string) data_get($blockRecipe, 'name'));
            $blocks = data_get($blockRecipe, 'blocks', []);

            if ($slug === '' || $name === '' || ! is_array($blocks)) {
                $skipped++;

                continue;
            }

            $existingBlock = PageBuilderReusableBlock::query()->where('slug', $slug)->first();

            if ($existingBlock) {
                $skipped++;

                continue;
            }

            PageBuilderReusableBlock::query()->create([
                'name' => $name,
                'slug' => $slug,
                'description' => trim((string) data_get($blockRecipe, 'description')) ?: 'Activated from ' . data_get($libraryItem, 'name') . '.',
                'blocks' => array_values($blocks),
                'settings' => [
                    'source' => 'library_activation',
                    'library_asset_key' => data_get($libraryItem, 'key'),
                    'library_asset_name' => data_get($libraryItem, 'name'),
                    'activation_contract' => $contract,
                ],
                'is_active' => (bool) data_get($blockRecipe, 'is_active', true),
            ]);

            $created++;
        }

        $activations = $this->libraryActivations();
        $activations[data_get($libraryItem, 'key')] = [
            'contract' => $contract,
            'created_reusable_blocks' => $created,
            'skipped_reusable_blocks' => $skipped,
            'activated_at' => now()->toDateTimeString(),
        ];

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_library_activations'],
            ['value' => json_encode($activations, JSON_UNESCAPED_SLASHES)]
        );

        site_setting_forget_cache();

        flash()->success("Activation finished. Created {$created} reusable block(s), skipped {$skipped}.");

        return redirect()->route('page-builder.plugins-theme.index');
    }

    protected function presetFromManifestItem(array $item, string $assetKey, string $name, string $description): array
    {
        $presetOverride = data_get($item, 'preset', []);
        $presetKey = Str::slug((string) data_get($presetOverride, 'key', data_get($item, 'related_preset_key') ?: $assetKey));
        $themeName = trim((string) data_get($presetOverride, 'family.theme_name', $name));
        $themeKey = Str::slug((string) data_get($presetOverride, 'family.theme_key', $presetKey));
        $coreKey = 'preset-' . $presetKey;
        $chromeKey = 'preset-' . $presetKey;

        $defaultPreset = [
            'key' => $presetKey,
            'name' => data_get($presetOverride, 'name', $name),
            'status' => data_get($presetOverride, 'status', 'imported'),
            'category' => 'theme',
            'description' => data_get($presetOverride, 'description', $description),
            'origin' => [
                'type' => 'manifest',
                'source' => data_get($item, 'package') ?: $assetKey,
                'note' => 'Imported from Plugins / Theme manifest. This is a controlled preset blueprint, not executable package code.',
            ],
            'family' => [
                'theme_key' => $themeKey,
                'theme_name' => $themeName,
                'supports_start_from_template' => true,
                'supports_custom_extension' => true,
                'recommended_library_asset_keys' => [$assetKey],
            ],
            'blueprint' => [
                'core_layout' => [
                    'key' => $coreKey,
                    'name' => 'Preset: ' . $name . ' Core',
                    'settings' => [],
                ],
                'chrome_layout' => [
                    'key' => $chromeKey,
                    'name' => 'Preset: ' . $name . ' Chrome',
                    'inherits_core_layout_key' => $coreKey,
                    'settings' => [],
                ],
                'starter_pages' => [
                    [
                        'key' => 'starter-landing',
                        'name' => $name . ' Starter Landing',
                        'purpose' => 'Starter landing page generated from imported theme manifest.',
                        'page_settings' => [
                            'layout_mode' => 'include',
                            'show_header' => true,
                            'show_footer' => true,
                            'content_mode' => 'builder',
                        ],
                        'block_recipe' => [],
                    ],
                ],
                'future_controls' => [
                    'content',
                    'page-settings',
                    'seo',
                    'builder-ads-info',
                    'limited-theme-overrides',
                ],
            ],
            'notes' => [
                'Imported manifest preset. Review generated Core Layout and Chrome Layout after instantiation.',
                'Package metadata is tracked for future activation, but no package code is executed yet.',
            ],
        ];

        return array_replace_recursive($defaultPreset, is_array($presetOverride) ? $presetOverride : []);
    }

    protected function activationFromManifestItem(array $item, string $assetKey): ?array
    {
        $activation = data_get($item, 'activation');

        if (! is_array($activation)) {
            return null;
        }

        $contract = trim((string) data_get($activation, 'contract'));

        if ($contract === '') {
            return null;
        }

        $blockTypes = collect(data_get($activation, 'block_types', []))
            ->filter(fn ($blockType) => is_string($blockType) && trim($blockType) !== '')
            ->map(fn (string $blockType) => Str::slug($blockType, '_'))
            ->values()
            ->all();

        $reusableBlocks = collect(data_get($activation, 'reusable_blocks', []))
            ->filter(fn ($blockRecipe) => is_array($blockRecipe))
            ->map(function (array $blockRecipe) use ($assetKey): array {
                $key = Str::slug((string) data_get($blockRecipe, 'key', data_get($blockRecipe, 'slug')));
                $slug = Str::slug((string) data_get($blockRecipe, 'slug', $assetKey . '-' . $key));

                return [
                    'key' => $key,
                    'name' => trim((string) data_get($blockRecipe, 'name')),
                    'slug' => $slug,
                    'description' => trim((string) data_get($blockRecipe, 'description')) ?: null,
                    'is_active' => (bool) data_get($blockRecipe, 'is_active', true),
                    'blocks' => is_array(data_get($blockRecipe, 'blocks')) ? array_values(data_get($blockRecipe, 'blocks')) : [],
                ];
            })
            ->values()
            ->all();

        return [
            'contract' => $contract,
            'block_types' => $blockTypes,
            'reusable_blocks' => $reusableBlocks,
        ];
    }

    protected function libraryActivations(): array
    {
        $raw = site_setting('page_builder_library_activations', []);

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function destroy(string $item): RedirectResponse
    {
        $libraryItem = $this->catalog->findOrFail($item);

        abort_unless(data_get($libraryItem, 'is_custom', false), 404);

        $customItems = $this->catalog->customItems()
            ->reject(fn ($customItem) => data_get($customItem, 'key') === data_get($libraryItem, 'key'))
            ->values()
            ->all();

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_library_custom_items'],
            ['value' => json_encode($customItems, JSON_UNESCAPED_SLASHES)]
        );

        $states = $this->catalog->stateOverrides();
        unset($states[data_get($libraryItem, 'key')]);

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_library_states'],
            ['value' => json_encode($states, JSON_UNESCAPED_SLASHES)]
        );

        site_setting_forget_cache();

        flash()->success('Custom library asset removed successfully.');

        return redirect()->route('page-builder.plugins-theme.index');
    }
}
