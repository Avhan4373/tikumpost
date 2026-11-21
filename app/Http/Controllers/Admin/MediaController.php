<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\ImageVariant;
use App\Support\UploadLimit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $media = Media::latest()->paginate(24);

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $maxLabel = UploadLimit::label();

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
            'alt_text' => ['nullable', 'string', 'max:150'],
        ], [
            'file.required' => 'Pilih berkas yang akan diunggah.',
            'file.uploaded' => "Berkas gagal diunggah. Ukurannya mungkin melebihi batas server ({$maxLabel}).",
            'file.mimes' => 'Format berkas harus JPG, PNG, WebP, atau PDF.',
            'file.max' => "Ukuran berkas maksimal {$maxLabel}.",
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');
        ImageVariant::ensureQuietly($path);

        $media = Media::create([
            'user_id' => $request->user()->id,
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $request->input('alt_text'),
        ]);

        // Editor WYSIWYG mengunggah gambar lewat fetch dan menerima URL JSON.
        if ($request->expectsJson()) {
            return response()->json([
                'id' => $media->id,
                'url' => $media->url,
                'alt_text' => $media->alt_text,
            ], 201);
        }

        return back()->with('status', 'Media berhasil diunggah.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('status', 'Media dihapus.');
    }
}
