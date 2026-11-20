<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use App\Models\DashboardSlide;
use App\Support\SvgSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EditDashboardController extends Controller
{
    private const INVALID_SVG_MESSAGE = 'File SVG tidak dapat dibaca. Pastikan file adalah SVG yang valid (ekspor ulang dari aplikasi desain), atau gunakan PNG.';

    /**
     * Display the Edit Dashboard management page.
     */
    public function index(): View
    {
        $logoUrl = DashboardSetting::getLogoUrl();
        $slides = DashboardSlide::query()->ordered()->get();

        return view('admin.edit-dashboard.index', compact('logoUrl', 'slides'));
    }

    /**
     * Save/update dashboard logo.
     */
    public function updateLogo(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:3072'],
        ], [
            'logo.required' => 'Silakan pilih berkas logo terlebih dahulu.',
            'logo.mimes' => 'Format logo harus berupa PNG, JPG, JPEG, WebP, atau SVG.',
            'logo.max' => 'Ukuran berkas logo maksimal 3MB.',
        ]);

        $file = $request->file('logo');

        $path = $this->storeImage($file, 'appearance/logo', 'logo_', 'png');

        if ($path === null) {
            $errorMsg = self::INVALID_SVG_MESSAGE;
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg], 422);
            }

            return back()->withErrors(['logo' => $errorMsg])->withInput();
        }

        // Hapus logo lama dari storage jika ada
        $oldLogo = DashboardSetting::getValue('app_logo');
        $this->deleteStoredFile($oldLogo);

        DashboardSetting::setValue('app_logo', $path);

        $msg = 'Logo dashboard berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'logo_url' => DashboardSetting::getLogoUrl(),
            ]);
        }

        return redirect()->route('admin.edit-dashboard.index', ['tab' => 'logo'])
            ->with('success', $msg);
    }

    /**
     * Reset the application logo to system default.
     */
    public function resetLogo(Request $request): RedirectResponse|JsonResponse
    {
        $currentLogo = DashboardSetting::getValue('app_logo');
        $this->deleteStoredFile($currentLogo);

        DashboardSetting::setValue('app_logo', null);

        $msg = 'Logo dashboard berhasil direset ke default sistem.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'logo_url' => null,
            ]);
        }

        return redirect()->route('admin.edit-dashboard.index', ['tab' => 'logo'])
            ->with('success', $msg);
    }

    /**
     * Store a new dashboard slide banner.
     */
    public function storeSlide(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:8192'],
            'title' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image.required' => 'Silakan pilih gambar slide terlebih dahulu.',
            'image.mimes' => 'Format gambar slide harus berupa PNG, JPG, JPEG, WebP, atau SVG.',
            'image.max' => 'Ukuran gambar slide maksimal 8MB.',
        ]);

        $file = $request->file('image');

        $path = $this->storeImage($file, 'appearance/slides', 'slide_', 'jpg');

        if ($path === null) {
            $errorMsg = self::INVALID_SVG_MESSAGE;
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg], 422);
            }

            return back()->withErrors(['image' => $errorMsg])->withInput();
        }

        $maxOrder = (int) DashboardSlide::max('sort_order');

        $slide = DashboardSlide::create([
            'title' => $data['title'] ?? null,
            'image_path' => $path,
            'sort_order' => $maxOrder + 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $msg = 'Slide banner berhasil ditambahkan.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'slide' => $slide,
            ]);
        }

        return redirect()->route('admin.edit-dashboard.index', ['tab' => 'slides'])
            ->with('success', $msg);
    }

    /**
     * Update an existing slide.
     */
    public function updateSlide(Request $request, DashboardSlide $slide): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:8192'],
            'title' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ], [
            'image.mimes' => 'Format gambar slide harus berupa PNG, JPG, JPEG, WebP, atau SVG.',
            'image.max' => 'Ukuran gambar slide maksimal 8MB.',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $newPath = $this->storeImage($file, 'appearance/slides', 'slide_', 'jpg');

            if ($newPath === null) {
                $errorMsg = self::INVALID_SVG_MESSAGE;
                if ($request->expectsJson()) {
                    return response()->json(['status' => 'error', 'message' => $errorMsg], 422);
                }

                return back()->withErrors(['image' => $errorMsg])->withInput();
            }

            $oldPath = $slide->image_path;
            $slide->image_path = $newPath;
            $this->deleteStoredFile($oldPath);
        }

        $slide->title = $data['title'] ?? null;
        $slide->is_active = $request->boolean('is_active');

        if (isset($data['sort_order'])) {
            $slide->sort_order = (int) $data['sort_order'];
        }

        $slide->save();

        $msg = 'Slide banner berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'slide' => $slide,
            ]);
        }

        return redirect()->route('admin.edit-dashboard.index', ['tab' => 'slides'])
            ->with('success', $msg);
    }

    /**
     * Delete a slide.
     */
    public function destroySlide(Request $request, DashboardSlide $slide): RedirectResponse|JsonResponse
    {
        $this->deleteStoredFile($slide->image_path);
        $slide->delete();

        $msg = 'Slide banner berhasil dihapus.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
            ]);
        }

        return redirect()->route('admin.edit-dashboard.index', ['tab' => 'slides'])
            ->with('success', $msg);
    }

    /**
     * Toggle active/inactive status of a slide.
     */
    public function toggleSlide(Request $request, DashboardSlide $slide): RedirectResponse|JsonResponse
    {
        $slide->update([
            'is_active' => ! $slide->is_active,
        ]);

        $statusText = $slide->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $msg = "Slide berhasil {$statusText}.";

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'is_active' => $slide->is_active,
            ]);
        }

        return redirect()->route('admin.edit-dashboard.index', ['tab' => 'slides'])
            ->with('success', $msg);
    }

    /**
     * Reorder slides.
     */
    public function reorderSlides(Request $request): RedirectResponse|JsonResponse
    {
        // Batch reordering
        if ($request->has('slide_ids') && is_array($request->input('slide_ids'))) {
            $slideIds = $request->input('slide_ids');
            foreach ($slideIds as $index => $id) {
                DashboardSlide::where('id', $id)->update(['sort_order' => $index + 1]);
            }

            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => 'Urutan slide berhasil diperbarui.']);
            }

            return redirect()->route('admin.edit-dashboard.index', ['tab' => 'slides'])
                ->with('success', 'Urutan slide berhasil diperbarui.');
        }

        // Single move (up/down)
        $request->validate([
            'slide_id' => ['required', 'exists:dashboard_slides,id'],
            'direction' => ['required', 'in:up,down'],
        ]);

        $currentSlide = DashboardSlide::findOrFail($request->input('slide_id'));
        $direction = $request->input('direction');

        if ($direction === 'up') {
            $adjacentSlide = DashboardSlide::where('sort_order', '<', $currentSlide->sort_order)
                ->orderBy('sort_order', 'desc')
                ->first();
        } else {
            $adjacentSlide = DashboardSlide::where('sort_order', '>', $currentSlide->sort_order)
                ->orderBy('sort_order', 'asc')
                ->first();
        }

        if ($adjacentSlide) {
            $tempOrder = $currentSlide->sort_order;
            $currentSlide->update(['sort_order' => $adjacentSlide->sort_order]);
            $adjacentSlide->update(['sort_order' => $tempOrder]);
        }

        $msg = 'Urutan slide berhasil disesuaikan.';

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => $msg]);
        }

        return redirect()->route('admin.edit-dashboard.index', ['tab' => 'slides'])
            ->with('success', $msg);
    }

    /**
     * Check if an uploaded file is SVG.
     */
    protected function isSvgFile(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = strtolower($file->getClientMimeType());

        return $extension === 'svg' || str_contains($mime, 'svg');
    }

    /**
     * Store an uploaded image under a random name. SVG files are cleaned
     * (scripts, event handlers, external references removed) and the cleaned
     * markup is what gets stored. Returns null when an SVG is not usable.
     */
    protected function storeImage(UploadedFile $file, string $directory, string $prefix, string $defaultExtension): ?string
    {
        if ($this->isSvgFile($file)) {
            $clean = SvgSanitizer::sanitize((string) file_get_contents($file->getRealPath()));
            if ($clean === null) {
                return null;
            }

            $path = $directory.'/'.$prefix.Str::random(20).'.svg';
            Storage::disk('public')->put($path, $clean);

            return $path;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $defaultExtension);

        return $file->storeAs($directory, $prefix.Str::random(20).'.'.$extension, 'public');
    }

    /**
     * Safely delete a file stored in local public disk if it exists.
     */
    protected function deleteStoredFile(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        $cleanPath = ltrim($path, '/');
        if (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }
    }
}
