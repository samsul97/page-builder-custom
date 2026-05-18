<?php

namespace Modules\PageBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilderMedia extends Model
{
    protected $table = 'pb_media';

    protected $fillable = [
        'disk',
        'directory',
        'filename',
        'original_name',
        'extension',
        'mime_type',
        'size',
        'path',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function url(): ?string
    {
        return uploads_url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function toPickerArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->original_name,
            'filename' => $this->filename,
            'path' => $this->path,
            'url' => $this->url(),
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'extension' => $this->extension,
            'created_at' => optional($this->created_at)->toISOString(),
            'is_image' => $this->isImage(),
        ];
    }

    public function usedBy(): array
    {
        $references = [];
        $path = (string) $this->path;

        foreach (PageBuilderPage::query()->select(['id', 'title', 'slug', 'blocks', 'og_image_path'])->get() as $page) {
            if ((string) $page->og_image_path === $path) {
                $references[] = [
                    'type' => 'page_seo',
                    'label' => 'Page SEO: ' . $page->title,
                ];
            }

            if ($this->arrayContainsPath($page->blocks, $path)) {
                $references[] = [
                    'type' => 'page_block',
                    'label' => 'Page Block: ' . $page->title,
                ];
            }
        }

        foreach (PageBuilderReusableBlock::query()->select(['id', 'name', 'blocks'])->get() as $block) {
            if ($this->arrayContainsPath($block->blocks, $path)) {
                $references[] = [
                    'type' => 'reusable_block',
                    'label' => 'Reusable Block: ' . $block->name,
                ];
            }
        }

        foreach (PageBuilderContentEntry::query()->select(['id', 'title', 'data'])->get() as $entry) {
            if ($this->arrayContainsPath($entry->data, $path)) {
                $references[] = [
                    'type' => 'content_entry',
                    'label' => 'Content Entry: ' . $entry->title,
                ];
            }
        }

        return array_values($references);
    }

    private function arrayContainsPath(mixed $value, string $path): bool
    {
        if (is_string($value)) {
            return $value === $path;
        }

        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if ($this->arrayContainsPath($item, $path)) {
                return true;
            }
        }

        return false;
    }
}
