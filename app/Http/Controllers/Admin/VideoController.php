<?php

namespace App\Http\Controllers\Admin;

use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PharData;
use RecursiveIteratorIterator;
use Throwable;

class VideoController extends ContentController
{
    private const MAX_VIDEO_SIZE_KB = 102400; // 100 MB
    private const MAX_INTERACTIVE_SIZE_KB = 39936; // 39 MB compressed (below PHP's 40 MB POST limit)
    private const MAX_INTERACTIVE_UNCOMPRESSED_BYTES = 209715200; // 200 MB
    private const MAX_INTERACTIVE_FILES = 2000;

    private const INTERACTIVE_EXTENSIONS = [
        'html', 'htm', 'css', 'js', 'json', 'txt', 'md', 'xml',
        'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'ico',
        'mp3', 'wav', 'ogg', 'm4a', 'aac', 'mp4', 'webm',
        'woff', 'woff2', 'ttf', 'otf', 'vtt',
    ];

    protected string $model = Video::class;

    protected string $view = 'videos';

    protected string $route = 'videos';

    protected ?string $uploadField = 'thumbnail';

    protected ?string $slugField = 'slug';

    protected array $rules = [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'type' => ['required', 'in:normal,interactive'],
        'video_url' => ['nullable', 'url', 'max:255'],
        'thumbnail' => ['nullable', 'image', 'max:2048'],
        'is_published' => ['nullable', 'boolean'],
        'published_at' => ['nullable', 'date'],
    ];

    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            Video::create($data);
        } catch (Throwable $exception) {
            $this->deleteInteractivePackage($data['interactive_path'] ?? null);
            throw $exception;
        }

        return redirect()->route('admin.videos.index')->with('success', 'Video berhasil ditambahkan.');
    }

    public function update(Request $request, $item)
    {
        $item = $this->findItem($item);
        $oldVideoPath = $item->video_path;
        $oldInteractivePath = $item->interactive_path;
        $data = $this->validated($request, $item);

        try {
            $item->update($data);
        } catch (Throwable $exception) {
            if (($data['interactive_path'] ?? null) !== $oldInteractivePath) {
                $this->deleteInteractivePackage($data['interactive_path'] ?? null);
            }
            throw $exception;
        }

        if (array_key_exists('video_path', $data)) {
            $this->deleteVideo($oldVideoPath);
        }
        if (array_key_exists('interactive_path', $data) && $data['interactive_path'] !== $oldInteractivePath) {
            $this->deleteInteractivePackage($oldInteractivePath);
        }

        return redirect()->route("admin.{$this->route}.index")->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($item)
    {
        $item = $this->findItem($item);
        $videoPath = $item->video_path;
        $interactivePath = $item->interactive_path;

        $this->deleteUpload($item);
        $item->delete();
        $this->deleteVideo($videoPath);
        $this->deleteInteractivePackage($interactivePath);

        return redirect()->route("admin.{$this->route}.index")->with('success', 'Data berhasil dihapus.');
    }

    protected function validated(Request $request, ?Model $item = null): array
    {
        $rules = $this->rules;
        $isInteractive = $request->input('type') === 'interactive';
        $rules['slug'] = ['nullable', 'string', 'max:255', 'unique:videos,slug'.($item ? ','.$item->id : '')];
        $rules['video'] = [
            Rule::requiredIf(! $isInteractive && ! $request->filled('video_url') && ! $item?->video_path),
            'nullable',
            'file',
            'mimes:mp4,webm,mov',
            'mimetypes:video/mp4,video/webm,video/quicktime',
            'max:'.self::MAX_VIDEO_SIZE_KB,
        ];
        $rules['interactive_zip'] = [
            Rule::requiredIf($isInteractive && ! $item?->interactive_path),
            'nullable',
            'file',
            'mimes:zip',
            'max:'.self::MAX_INTERACTIVE_SIZE_KB,
        ];

        $data = $request->validate($rules, [
            'video.required_without' => 'Unggah file video, atau isi link video (Instagram/YouTube/sumber lain).',
            'video.uploaded' => 'File video gagal diunggah. Pastikan ukuran file tidak melebihi batas server.',
            'video.mimes' => 'File video harus berformat MP4, WebM, atau MOV.',
            'video.mimetypes' => 'Tipe file video tidak valid. Gunakan MP4, WebM, atau MOV.',
            'video.max' => 'Ukuran video maksimal 100 MB.',
            'interactive_zip.required' => 'Unggah paket ZIP untuk video interaktif.',
            'interactive_zip.mimes' => 'Paket video interaktif harus berupa file ZIP.',
            'interactive_zip.max' => 'Ukuran ZIP interaktif maksimal 39 MB.',
        ]);

        unset($data['video'], $data['interactive_zip']);
        $data['is_published'] = $request->boolean('is_published');
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        if (! $data['published_at'] && $data['is_published']) {
            $data['published_at'] = now();
        }

        if ($isInteractive) {
            $data['video_url'] = 'interactive://'.$data['slug'];
            $data['video_path'] = null;

            if ($request->hasFile('interactive_zip')) {
                $data['interactive_path'] = $this->storeInteractivePackage($request);
            }
        } else {
            $data['interactive_path'] = null;

            if ($request->hasFile('video')) {
            // UploadedFile::store generates a safe, unique filename and never uses the client filename.
                $data['video_path'] = $request->file('video')->store('videos', 'public');
            }
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

    private function storeInteractivePackage(Request $request): string
    {
        $packageId = (string) Str::uuid();
        $temporaryName = $packageId.'.zip';
        $temporaryPath = $request->file('interactive_zip')->storeAs('interactive-video-temp', $temporaryName, 'local');

        if (! $temporaryPath) {
            throw ValidationException::withMessages(['interactive_zip' => 'ZIP gagal disimpan untuk diproses.']);
        }

        $absolutePath = Storage::disk('local')->path($temporaryPath);
        $destination = 'interactive-videos/'.$packageId;

        try {
            $archive = new PharData($absolutePath);
            $prefix = 'phar://'.str_replace('\\', '/', $absolutePath).'/';
            $entries = [];
            $indexFiles = [];
            $totalBytes = 0;

            foreach (new RecursiveIteratorIterator($archive) as $uri => $file) {
                if ($file->isDir()) {
                    continue;
                }

                $relativePath = str_replace('\\', '/', substr(str_replace('\\', '/', (string) $uri), strlen($prefix)));
                $segments = explode('/', $relativePath);
                $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

                if ($relativePath === '' || str_starts_with($relativePath, '/') || str_contains($relativePath, "\0") || in_array('..', $segments, true) || str_contains($relativePath, ':')) {
                    throw ValidationException::withMessages(['interactive_zip' => 'ZIP berisi path file yang tidak aman.']);
                }

                if (str_starts_with($relativePath, '__MACOSX/') || basename($relativePath) === '.DS_Store') {
                    continue;
                }

                if (! in_array($extension, self::INTERACTIVE_EXTENSIONS, true)) {
                    throw ValidationException::withMessages(['interactive_zip' => "Jenis file .{$extension} tidak diizinkan di dalam ZIP."]);
                }

                $totalBytes += $file->getSize();
                $entries[] = [$relativePath, (string) $uri];

                if (strtolower(basename($relativePath)) === 'index.html') {
                    $indexFiles[] = $relativePath;
                }

                if (count($entries) > self::MAX_INTERACTIVE_FILES || $totalBytes > self::MAX_INTERACTIVE_UNCOMPRESSED_BYTES) {
                    throw ValidationException::withMessages(['interactive_zip' => 'Isi ZIP terlalu besar atau memiliki terlalu banyak file.']);
                }
            }

            if ($indexFiles === []) {
                throw ValidationException::withMessages(['interactive_zip' => 'ZIP harus memiliki file index.html.']);
            }

            usort($indexFiles, fn (string $a, string $b) => substr_count($a, '/') <=> substr_count($b, '/'));

            foreach ($entries as [$relativePath, $uri]) {
                $contents = file_get_contents($uri);
                if ($contents === false || ! Storage::disk('public')->put($destination.'/'.$relativePath, $contents)) {
                    throw ValidationException::withMessages(['interactive_zip' => 'Salah satu file dalam ZIP gagal diekstrak.']);
                }
            }

            return $destination.'/'.$indexFiles[0];
        } catch (ValidationException $exception) {
            Storage::disk('public')->deleteDirectory($destination);
            throw $exception;
        } catch (Throwable $exception) {
            Storage::disk('public')->deleteDirectory($destination);
            throw ValidationException::withMessages(['interactive_zip' => 'ZIP tidak valid atau rusak.']);
        } finally {
            Storage::disk('local')->delete($temporaryPath);
        }
    }

    private function deleteInteractivePackage(?string $path): void
    {
        if (! $path || ! Str::startsWith($path, 'interactive-videos/')) {
            return;
        }

        $parts = explode('/', $path);
        if (count($parts) >= 2 && Str::isUuid($parts[1])) {
            Storage::disk('public')->deleteDirectory($parts[0].'/'.$parts[1]);
        }
    }
}
