<?php

namespace Modules\PageBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\PageBuilder\Http\Requests\UploadPageBuilderMediaRequest;
use Modules\PageBuilder\Models\PageBuilderMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageBuilderMediaController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        $mediaQuery = PageBuilderMedia::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($inner) use ($query) {
                    $inner->where('original_name', 'like', '%' . $query . '%')
                        ->orWhere('filename', 'like', '%' . $query . '%')
                        ->orWhere('mime_type', 'like', '%' . $query . '%');
                });
            })
            ->latest();

        if ($request->boolean('json')) {
            $media = $mediaQuery->limit(120)->get();

            return response()->json([
                'media' => $media->map(function (PageBuilderMedia $item) {
                    $usedBy = $item->usedBy();

                    return array_merge($item->toPickerArray(), [
                        'used_by' => $usedBy,
                        'used_by_count' => count($usedBy),
                    ]);
                })->values(),
            ]);
        }

        $media = $mediaQuery->paginate(24)->withQueryString();

        return view('pagebuilder::media.index', [
            'media' => $media,
            'query' => $query,
        ]);
    }

    public function store(UploadPageBuilderMediaRequest $request): JsonResponse|RedirectResponse
    {
        $file = $request->file('file');
        $directory = 'page-builder/images';
        $filename = store_upload($file, $directory);
        $path = $directory . '/' . $filename;

        $media = PageBuilderMedia::create([
            'disk' => 'uploads',
            'directory' => $directory,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->guessExtension(),
            'mime_type' => $file->getMimeType(),
            'size' => (int) $file->getSize(),
            'path' => $path,
        ]);

        $payload = [
            'success' => true,
            'path' => $media->path,
            'url' => $media->url(),
            'name' => $media->original_name,
            'media' => $media->toPickerArray(),
        ];

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($payload);
        }

        flash()->success('Media uploaded successfully.');

        return redirect()->route('page-builder.media.index');
    }

    public function destroy(Request $request, PageBuilderMedia $pageBuilderMedia): JsonResponse|RedirectResponse
    {
        $references = $pageBuilderMedia->usedBy();

        if (!empty($references)) {
            $message = 'This media file is still used by ' . count($references) . ' item(s). Remove those references before deleting it.';

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'used_by' => $references,
                ], 422);
            }

            flash()->error($message);

            return redirect()->route('page-builder.media.index');
        }

        delete_upload($pageBuilderMedia->path);
        $pageBuilderMedia->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        flash()->success('Media deleted successfully.');

        return redirect()->route('page-builder.media.index');
    }
}
