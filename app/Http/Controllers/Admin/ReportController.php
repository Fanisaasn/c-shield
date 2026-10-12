<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const LEVELS = ['Sangat Rendah', 'Rendah', 'Cukup', 'Baik', 'Sangat Baik'];

    public function index(Request $request)
    {
        $attempts = $this->filteredAttempts($request)->paginate(20)->withQueryString();

        $webinarRegistrations = WebinarRegistration::with('webinar')
            ->latest()
            ->paginate(20, ['*'], 'wr_page')
            ->withQueryString();

        return view('admin.reports.index', [
            'attempts' => $attempts,
            'levels' => self::LEVELS,
            'webinarRegistrations' => $webinarRegistrations,
        ]);
    }

    /**
     * Export assessment data as CSV.
     */
    public function exportAssessment(Request $request): StreamedResponse
    {
        $attempts = $this->filteredAttempts($request)->get();

        $filename = 'laporan-assessment-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($attempts) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Nama Peserta',
                'Jenis Kelamin',
                'Usia',
                'Pendidikan',
                'Domisili',
                'Status Pekerjaan',
                'Tipe',
                'Skor',
                'Tingkat',
                'Tanggal Pengerjaan',
            ]);

            foreach ($attempts as $index => $attempt) {
                fputcsv($handle, [
                    $index + 1,
                    $attempt->user?->name ?? '-',
                    $attempt->user?->gender ?? '-',
                    $attempt->user?->age ?? '-',
                    $attempt->user?->education ?? '-',
                    $attempt->user?->domicile ?? '-',
                    $attempt->user?->occupation_status ?? '-',
                    strtoupper($attempt->type),
                    $attempt->score,
                    $attempt->level,
                    $attempt->completed_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export webinar registrations as CSV.
     */
    public function exportWebinarRegistrations(Request $request): StreamedResponse
    {
        $query = WebinarRegistration::with('webinar')->latest();

        if ($request->filled('webinar_id')) {
            $query->where('webinar_id', $request->webinar_id);
        }

        $registrations = $query->get();

        $filename = 'peserta-webinar-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Webinar',
                'Nama',
                'Email',
                'No. Telepon',
                'Instansi',
                'Pekerjaan',
                'Motivasi',
                'Tanggal Daftar',
            ]);

            foreach ($registrations as $index => $reg) {
                fputcsv($handle, [
                    $index + 1,
                    $reg->webinar?->title ?? '-',
                    $reg->name,
                    $reg->email,
                    $reg->phone ?? '-',
                    $reg->institution ?? '-',
                    $reg->occupation ?? '-',
                    $reg->motivation ?? '-',
                    $reg->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Build the filtered query for assessment attempts.
     */
    private function filteredAttempts(Request $request)
    {
        return AssessmentAttempt::with('user')
            ->whereNotNull('completed_at')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('level'), fn ($q) => $q->where('level', $request->level))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('completed_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('completed_at', '<=', $request->to))
            ->latest('completed_at');
    }
}
