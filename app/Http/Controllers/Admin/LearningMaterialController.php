<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Support\InteractivePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class LearningMaterialController extends Controller
{
    private const MAX_FILE_SIZE_KB = 102400; // 100 MB

    private const INTERACTIVE_DIRECTORY = 'learning-materials/interactive';

    /**
     * File extension => material type shown to the respondent. PPT/PPTX is
     * not accepted for new uploads: its pages cannot be tracked in the
     * browser, so the respondent could not be required to read it to the end.
     */
    private const FILE_TYPES = [
        'pdf' => 'pdf',
        'mp4' => 'video',
        'webm' => 'video',
    ];

    /**
     * List every study material in the order respondents go through them.
     */
    public function index()
    {
        $materials = LearningMaterial::ordered();

        return view('admin.learning-material.index', compact('materials'));
    }

    public function create()
    {
        $material = new LearningMaterial(['file_type' => 'pdf']);

        return view('admin.learning-material.form', compact('material'));
    }

    public function store(Request $request): RedirectResponse
    {
        $material = new LearningMaterial([
            'sort_order' => (int) LearningMaterial::max('sort_order') + 1,
        ]);

        $this->save($request, $material);

        return redirect()->route('admin.learning-material.index')->with('success', 'Materi belajar berhasil ditambahkan.');
    }

    public function edit(LearningMaterial $material)
    {
        return view('admin.learning-material.form', compact('material'));
    }

    public function update(Request $request, LearningMaterial $material): RedirectResponse
    {
        $this->save($request, $material);

        return redirect()->route('admin.learning-material.index')->with('success', 'Materi belajar berhasil diperbarui.');
    }

    public function destroy(LearningMaterial $material): RedirectResponse
    {
        $path = $material->file_path;
        $type = $material->file_type;

        $material->delete();
        $this->deleteFile($path, $type);

        return redirect()->route('admin.learning-material.index')->with('success', 'Materi belajar berhasil dihapus.');
    }

    /**
     * Swap a material with its neighbour above or below it.
     */
    public function move(Request $request, LearningMaterial $material): RedirectResponse
    {
        $direction = $request->validate(['direction' => 'required|in:up,down'])['direction'];
        $materials = LearningMaterial::ordered()->values();
        $index = $materials->search(fn (LearningMaterial $item) => $item->is($material));
        $neighbour = $materials->get($direction === 'up' ? $index - 1 : $index + 1);

        if ($neighbour) {
            DB::transaction(function () use ($materials, $material, $neighbour) {
                // Renumber everything so materials sharing a sort_order still swap cleanly.
                $order = $materials->pluck('id')->all();
                $a = array_search($material->id, $order, true);
                $b = array_search($neighbour->id, $order, true);
                [$order[$a], $order[$b]] = [$order[$b], $order[$a]];

                foreach ($order as $position => $id) {
                    LearningMaterial::whereKey($id)->update(['sort_order' => $position + 1]);
                }
            });
        }

        return back();
    }

    /**
     * Validate the form and create or update the material, replacing its
     * file (or interactive package) when a new one is uploaded, or pointing
     * it at a YouTube/Instagram video link.
     */
    private function save(Request $request, LearningMaterial $material): void
    {
        $kind = $request->input('kind');
        $hasUploadedFile = $material->file_path && ! $material->isInteractive() && ! $material->isLink();
        $hasPackage = $material->file_path && $material->isInteractive();
        $linkType = LearningMaterial::linkTypeOf((string) $request->input('external_url'));

        $data = $request->validate([
            'kind' => ['required', 'in:file,interactive,link'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'file' => [
                Rule::requiredIf($kind === 'file' && ! $hasUploadedFile),
                'nullable',
                'file',
                'extensions:'.implode(',', array_keys(self::FILE_TYPES)),
                'max:'.self::MAX_FILE_SIZE_KB,
            ],
            'interactive_zip' => [
                Rule::requiredIf($kind === 'interactive' && ! $hasPackage),
                'nullable',
                'file',
                'mimes:zip',
                'max:'.InteractivePackage::MAX_SIZE_KB,
            ],
            'external_url' => [
                Rule::requiredIf($kind === 'link'),
                'nullable',
                'url',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail) use ($kind, $linkType) {
                    if ($kind === 'link' && $linkType === null) {
                        $fail('Link harus berupa video YouTube atau Instagram (post/reel).');
                    }
                },
            ],
            // m:ss or plain seconds. Instagram cannot report playback, so its length is required.
            'link_duration' => [
                Rule::requiredIf($kind === 'link' && $linkType === 'instagram'),
                'nullable',
                'regex:/^\s*(\d+:[0-5]?\d|\d+)\s*$/',
            ],
        ], [
            'file.required' => 'Unggah file materi (PDF atau video MP4/WebM).',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukuran file tidak melebihi batas server.',
            'file.extensions' => 'File harus berformat PDF, MP4, atau WebM. Untuk PPT/PPTX, simpan dulu sebagai PDF.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'interactive_zip.required' => 'Unggah paket ZIP untuk video interaktif.',
            'interactive_zip.mimes' => 'Paket video interaktif harus berupa file ZIP.',
            'interactive_zip.max' => 'Ukuran ZIP interaktif maksimal 39 MB.',
            'external_url.required' => 'Isi link video YouTube atau Instagram.',
            'external_url.url' => 'Link video tidak valid.',
            'link_duration.required' => 'Isi durasi video Instagram, misalnya 1:30.',
            'link_duration.regex' => 'Durasi ditulis sebagai menit:detik (misalnya 1:30) atau jumlah detik.',
        ]);

        $attributes = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ];
        $oldPath = $material->file_path;
        $oldType = $material->file_type;
        $newPath = null;

        if ($kind === 'link') {
            $attributes += [
                'external_url' => trim($data['external_url']),
                'file_type' => $linkType,
                'file_path' => null,
                'original_name' => null,
                'duration_seconds' => $this->parseDuration($data['link_duration'] ?? null),
            ];
        } elseif ($kind === 'file' && $request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            // Store under a random name; the client filename is only kept for display.
            $newPath = $file->storeAs('learning-materials', Str::random(40).'.'.$extension, 'public');
            $attributes += [
                'file_path' => $newPath,
                'external_url' => null,
                'file_type' => self::FILE_TYPES[$extension],
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                // Measured by the admin's browser from the chosen file; used to check the respondent watched it all.
                'duration_seconds' => self::FILE_TYPES[$extension] === 'video' ? ($data['duration_seconds'] ?? null) : null,
            ];
        } elseif ($kind === 'interactive' && $request->hasFile('interactive_zip')) {
            $zip = $request->file('interactive_zip');
            $newPath = InteractivePackage::store($zip, self::INTERACTIVE_DIRECTORY, 'interactive_zip');
            $attributes += [
                'file_path' => $newPath,
                'external_url' => null,
                'file_type' => 'interactive',
                'original_name' => mb_substr($zip->getClientOriginalName(), 0, 255),
                'duration_seconds' => null,
            ];
        }

        try {
            $material->fill($attributes)->save();
        } catch (Throwable $exception) {
            $this->deleteFile($newPath, $attributes['file_type'] ?? null);
            throw $exception;
        }

        // The old file is no longer used once replaced by a new file or by a link.
        if ($oldPath && ($newPath || $kind === 'link')) {
            $this->deleteFile($oldPath, $oldType);
        }
    }

    /**
     * "m:ss" or plain seconds => seconds; null when empty.
     */
    private function parseDuration(?string $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_contains($value, ':')) {
            [$minutes, $seconds] = explode(':', $value);

            return (int) $minutes * 60 + (int) $seconds;
        }

        return (int) $value;
    }

    private function deleteFile(?string $path, ?string $type): void
    {
        if (! $path) {
            return;
        }

        if ($type === 'interactive') {
            InteractivePackage::delete($path, self::INTERACTIVE_DIRECTORY);
        } else {
            Storage::disk('public')->delete($path);
        }
    }
}
