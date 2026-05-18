<?php

namespace Modules\PageBuilder\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageBuilderBlockTypeRegistry
{
    public function all(): Collection
    {
        $stateOverrides = $this->stateOverrides();

        return collect(config('page_builder_block_types.items', []))
            ->map(function (array $item) use ($stateOverrides): array {
                $type = data_get($item, 'type');

                return [
                    ...$item,
                    'default_status' => data_get($item, 'status', 'enabled'),
                    'status' => $stateOverrides[$type] ?? data_get($item, 'status', 'enabled'),
                ];
            });
    }

    public function enabled(): Collection
    {
        return $this->all()
            ->where('status', 'enabled')
            ->values();
    }

    public function disabledTypes(): array
    {
        return $this->all()
            ->where('status', 'disabled')
            ->pluck('type')
            ->values()
            ->all();
    }

    public function usedDisabledTypes(array $blocks): array
    {
        $disabledTypes = $this->disabledTypes();

        if (count($disabledTypes) === 0) {
            return [];
        }

        return collect($blocks)
            ->pluck('type')
            ->filter()
            ->unique()
            ->intersect($disabledTypes)
            ->values()
            ->all();
    }

    public function toggle(string $type): array
    {
        $item = $this->findOrFail($type);
        $states = $this->stateOverrides();
        $nextStatus = data_get($item, 'status') === 'enabled' ? 'disabled' : 'enabled';

        $states[data_get($item, 'type')] = $nextStatus;

        SiteSetting::query()->updateOrCreate(
            ['key' => 'page_builder_block_type_states'],
            ['value' => json_encode($states, JSON_UNESCAPED_SLASHES)]
        );

        site_setting_forget_cache();

        return [
            ...$item,
            'status' => $nextStatus,
        ];
    }

    public function findOrFail(string $type): array
    {
        $item = $this->all()->firstWhere('type', $type);

        if (! $item) {
            throw new NotFoundHttpException();
        }

        return $item;
    }

    public function stateOverrides(): array
    {
        $raw = site_setting('page_builder_block_type_states', []);

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
