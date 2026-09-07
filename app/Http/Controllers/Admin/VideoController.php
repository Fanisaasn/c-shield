<?php

namespace App\Http\Controllers\Admin;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends ContentController
{
    private const MAX_VIDEO_SIZE_KB = 102400; // 100 MB

    protected string $model = Video::class;

    protected string $view = 'videos';

    protected string $route = 'videos';

    protected ?string $uploadField = 'thumbnail';

    protected ?string $slugField = 'slug';

    protected array $rules = [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'thumbnail' => ['nullable', 'image', 'max:2048'],
        'is_published' => ['nullable', 'boolean'],
        'published_at' => ['nullable', 'date'],
    ];

    public function update(Request $request, $item)
    {
        $item = $this->findItem($item);
        $oldVideoPath = $item->video_path;
        $data = $this->validated($request, $item);

        $item->update($data);

        if (array_key_exists('video_path', $data)) {
            $this->deleteVideo($oldVideoPath);
        }

        return redirect()->route("admin.{$this->route}.index")->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($item)
    {
        $item = $this->findItem($item);
        $videoPath = $item->video_path;

        $this->deleteUpload($item);
        $item->delete();
        $this->deleteVideo($videoPath);

        return redirect()->route("admin.{$this->route}.index")->with('success', 'Data berhasil dihapus.');
    }

    protected function validated(Request $request, ?Model $item = null): array
    {
        $rules = $this->rules;
        $rules['slug'] = ['nullable', 'string', 'max:255', 'unique:videos,slug'.($item ? ','.$item->id : '')];
        $rules['video'] = [
            $item ? 'nullable' : 'required',
            'file',
            'mimes:mp4,webm,mov',
            'mimetypes:video/mp4,video/webm,video/quicktime',
            'max:'.self::MAX_VIDEO_SIZE_KB,
        ];

        $data = $request->validate($rules, [
            'video.required' => 'File video wajib diunggah.',
            'video.uploaded' => 'File video gagal diunggah. Pastikan ukuran file tidak melebihi batas server.',
            'video.mimes' => 'File video harus berformat MP4, WebM, atau MOV.',
            'video.mimetypes' => 'Tipe file video tidak valid. Gunakan MP4, WebM, atau MOV.',
            'video.max' => 'Ukuran video maksimal 100 MB.',
        ]);

        unset($data['video']);
        $data['is_published'] = $request->boolean('is_published');
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        if (! $data['published_at'] && $data['is_published']) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('video')) {
            // UploadedFile::store generates a safe, unique filename and never uses the client filename.
            $data['video_path'] = $request->file('video')->store('videos', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $this->deleteUpload($item);
            $data['thumbnail'] = $request->file('thumbnail')->store('videos', 'public');
        }

        return $data;
    }

    private function deleteVideo(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
