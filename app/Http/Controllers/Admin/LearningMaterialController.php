<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LearningMaterialController extends Controller
{
    private const MAX_FILE_SIZE_KB = 102400; // 100 MB

    /**
     * File extension => material type shown to the respondent.
     */
    private const FILE_TYPES = [
        'pdf' => 'pdf',
        'ppt' => 'ppt',
        'pptx' => 'ppt',
        'mp4' => 'video',
        'webm' => 'video',
    ];

    /**
     * Show the form for the single Social Engineering study material.
     */
    public function index()
    {
        $material = LearningMaterial::current() ?? new LearningMaterial([
            'title' => 'Materi Social Engineering',
        ]);

        return view('admin.learning-material.index', compact('material'));
    }

    /**
     * Create or update the single study material, replacing its file when
     * a new one is uploaded.
     */
    public function update(Request $request): RedirectResponse
    {
        $material = LearningMaterial::current() ?? new LearningMaterial();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => [
                Rule::requiredIf(! $material->file_path),
                'nullable',
                'file',
                'extensions:'.implode(',', array_keys(self::FILE_TYPES)),
                'max:'.self::MAX_FILE_SIZE_KB,
            ],
        ], [
            'file.required' => 'Unggah file materi (PDF, PPT/PPTX, atau video MP4/WebM).',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukuran file tidak melebihi batas server.',
            'file.extensions' => 'File harus berformat PDF, PPT, PPTX, MP4, atau WebM.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
        ]);

        unset($data['file']);
        $oldPath = $material->file_path;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());

            // Store under a random name; the client filename is only kept for display.
            $data['file_path'] = $file->storeAs('learning-materials', Str::random(40).'.'.$extension, 'public');
            $data['file_type'] = self::FILE_TYPES[$extension];
            $data['original_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
        }

        $material->fill($data)->save();

        if (isset($data['file_path']) && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', 'Materi belajar berhasil disimpan.');
    }
}
