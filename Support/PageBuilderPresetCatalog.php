<?php

namespace Modules\PageBuilder\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageBuilderPresetCatalog
{
    public function all(): Collection
    {
        return collect(config('page_builder_presets.presets', []))
            ->concat($this->customPresets())
            ->map(function (array $preset): array {
                return [
                    ...$preset,
                    'starter_page_count' => count(data_get($preset, 'blueprint.starter_pages', [])),
                    'future_control_count' => count(data_get($preset, 'blueprint.future_controls', [])),
                ];
            });
    }

    public function findOrFail(string $key): array
    {
        $preset = $this->all()->firstWhere('key', $key);

        if (! $preset) {
            throw new NotFoundHttpException();
        }

        return Arr::undot($preset);
    }

    public function customPresets(): Collection
    {
        $raw = site_setting('page_builder_custom_presets', []);

        if (is_array($raw)) {
            return collect($raw)->filter(fn ($preset) => is_array($preset));
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                return collect($decoded)->filter(fn ($preset) => is_array($preset));
            }
        }

        return collect();
    }
}
