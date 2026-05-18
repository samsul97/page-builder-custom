<?php

namespace Modules\PageBuilder\Support;

use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageBuilderLibraryCatalog
{
    public function all(): Collection
    {
        $stateOverrides = $this->stateOverrides();
        $customItems = $this->customItems();

        return collect(config('page_builder_library.items', []))
            ->concat($customItems)
            ->map(function (array $item) use ($stateOverrides): array {
                $key = data_get($item, 'key');

                return [
                    ...$item,
                    'is_custom' => (bool) data_get($item, 'is_custom', false),
                    'default_status' => data_get($item, 'status', 'planned'),
                    'status' => $stateOverrides[$key] ?? data_get($item, 'status', 'planned'),
                ];
            });
    }

    public function grouped(): Collection
    {
        return $this->all()->groupBy('category')->sortKeys();
    }

    public function forPreset(?string $presetKey): Collection
    {
        if (! filled($presetKey)) {
            return collect();
        }

        return $this->all()
            ->filter(fn (array $item) => data_get($item, 'related_preset_key') === $presetKey)
            ->values();
    }

    public function enabledForPreset(?string $presetKey): Collection
    {
        return $this->forPreset($presetKey)
            ->where('status', 'enabled')
            ->values();
    }

    public function findOrFail(string $key): array
    {
        $item = $this->all()->firstWhere('key', $key);

        if (! $item) {
            throw new NotFoundHttpException();
        }

        return $item;
    }

    public function stateOverrides(): array
    {
        $raw = site_setting('page_builder_library_states', []);

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function customItems(): Collection
    {
        $raw = site_setting('page_builder_library_custom_items', []);

        if (is_array($raw)) {
            return collect($raw)->filter(fn ($item) => is_array($item));
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                return collect($decoded)->filter(fn ($item) => is_array($item));
            }
        }

        return collect();
    }
}
