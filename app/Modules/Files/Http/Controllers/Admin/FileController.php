<?php

namespace App\Modules\Files\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Files\Models\File;
use App\Modules\Files\Services\MorphFileSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FileController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('files-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $type = trim((string) $request->query('type', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = File::query()->orderByDesc('id');

        if ($type !== '') {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%");
            });
        }

        $files = $query->paginate($perPage)->withQueryString()
            ->through(fn (File $file) => [
                'id' => $file->id,
                'name' => $file->name,
                'type' => $file->type,
                'extension' => $file->extension,
                'size' => $file->size,
                'url' => $file->publicUrl(),
                'fileable_type' => $file->fileable_type ? class_basename($file->fileable_type) : null,
                'fileable_id' => $file->fileable_id,
            ]);

        return Inertia::render('Admin/Files/Index', [
            'files' => $files,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'per_page' => $perPage,
            ],
            'typeOptions' => [
                ['value' => 'image', 'label' => 'تصویر'],
                ['value' => 'gallery', 'label' => 'گالری'],
                ['value' => 'document', 'label' => 'سند'],
            ],
            'abilities' => [
                'delete' => $request->user()?->can('files-delete') ?? false,
            ],
        ]);
    }

    public function destroy(Request $request, File $file, MorphFileSyncService $files): RedirectResponse
    {
        abort_unless($request->user()?->can('files-delete'), 403);

        $files->deleteFile($file);

        return back()->with('success', 'فایل حذف شد.');
    }
}
