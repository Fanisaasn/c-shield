<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use App\Models\AssessmentVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssessmentVideoController extends Controller
{
    private const MAX_VIDEO_SIZE_KB = 102400; // 100 MB

    /**
     * List every "Video Materi" entry, together with the assessment themes
     * available to attach a video to. A video left without a theme (Umum)
     * is the fallback shown when the chosen theme has no active video of
     * its own. Within a theme, only one video can be active at a time.
     */
    public function index()
    {
        $videos = AssessmentVideo::query()->with('category')->latest()->get();
        $categories = AssessmentCategory::query()->orderBy('order')->get();

        return view('admin.assessment-videos.index', compact('videos', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        if ($request->hasFile('video')) {
            // UploadedFile::store generates a safe, unique filename and never uses the client filename.
            $data['video_path'] = $request->file('video')->store('assessment-videos', 'public');
        }

        AssessmentVideo::create($data);

        return back()->with('success', 'Video materi ditambahkan.');
    }

    public function update(Request $request, AssessmentVideo $video): RedirectResponse
    {
        $data = $this->validated($request, $video);
        $oldPath = $video->video_path;

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('assessment-videos', 'public');
        }

        $video->update($data);

        if ($request->hasFile('video')) {
            $this->deleteVideoFile($oldPath);
        }

        return back()->with('success', 'Video materi diperbarui.');
    }

    /**
     * Make this the active video for its theme (or, for a video with no
     * theme, the active fallback video), deactivating every other entry
     * within that same scope.
     */
    public function activate(AssessmentVideo $video): RedirectResponse
    {
        AssessmentVideo::query()
            ->where('is_active', true)
            ->when(
                $video->assessment_category_id,
                fn ($query, $categoryId) => $query->where('assessment_category_id', $categoryId),
                fn ($query) => $query->whereNull('assessment_category_id'),
            )
            ->update(['is_active' => false]);

        $video->update(['is_active' => true]);

        return back()->with('success', 'Video materi ini kini aktif digunakan pada alur Self-Assessment.');
    }

    public function destroy(AssessmentVideo $video): RedirectResponse
    {
        $this->deleteVideoFile($video->video_path);
        $video->delete();

        return back()->with('success', 'Video materi dihapus.');
    }

    private function validated(Request $request, ?AssessmentVideo $video): array
    {
        $hasExistingSource = $video && ($video->video_path || $video->video_url);

        $data = $request->validate([
            'assessment_category_id' => ['nullable', 'exists:assessment_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'video' => [
                $hasExistingSource ? 'nullable' : 'required_without:video_url',
                'nullable',
                'file',
                'mimes:mp4,webm,mov',
                'mimetypes:video/mp4,video/webm,video/quicktime',
                'max:'.self::MAX_VIDEO_SIZE_KB,
            ],
        ], [
            'video.required_without' => 'Unggah file video, atau isi link video (YouTube/sumber lain).',
            'video.mimes' => 'File video harus berformat MP4, WebM, atau MOV.',
            'video.mimetypes' => 'Tipe file video tidak valid. Gunakan MP4, WebM, atau MOV.',
            'video.max' => 'Ukuran video maksimal 100 MB.',
        ]);

        unset($data['video']);

        return $data;
    }

    private function deleteVideoFile(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
