<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WebinarLinkMail;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class WebinarRegistrationController extends Controller
{
    /**
     * Mailer yang tidak benar-benar mengirim email (hanya menulis ke log / memori).
     */
    private const NON_DELIVERING_MAILERS = ['log', 'array'];

    /**
     * Display the webinar registrations management page grouped by webinar.
     */
    public function index(Request $request): View
    {
        // 1. Kelompokkan pendaftar berdasarkan judul webinar
        $webinars = Webinar::query()
            ->withCount([
                'registrations',
                'registrations as pending_count' => fn ($q) => $q->where('email_status', 'pending'),
                'registrations as sent_count' => fn ($q) => $q->where('email_status', 'sent'),
                'registrations as failed_count' => fn ($q) => $q->where('email_status', 'failed'),
            ])
            ->orderByDesc('webinar_date')
            ->get();

        // 2. Webinar yang sedang dipilih / diklik
        $selectedWebinarId = $request->query('webinar_id');
        $selectedWebinar = null;

        if ($selectedWebinarId) {
            $selectedWebinar = $webinars->firstWhere('id', (int) $selectedWebinarId);
        }

        // Default to first webinar if none selected
        if (! $selectedWebinar && $webinars->isNotEmpty()) {
            $selectedWebinar = $webinars->first();
        }

        // 3. Daftar pendaftar untuk webinar yang dipilih
        $registrations = collect();
        if ($selectedWebinar) {
            $query = WebinarRegistration::query()
                ->where('webinar_id', $selectedWebinar->id);

            // Filter pencarian
            if ($request->filled('search')) {
                $search = trim($request->query('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('referral_source', 'like', "%{$search}%")
                        ->orWhere('institution', 'like', "%{$search}%");
                });
            }

            // Filter status email
            if ($request->filled('status') && in_array($request->query('status'), ['pending', 'sent', 'failed'])) {
                $query->where('email_status', $request->query('status'));
            }

            $registrations = $query->orderByDesc('id')->get();
        }

        return view('admin.webinar-registrations.index', [
            'webinars' => $webinars,
            'selectedWebinar' => $selectedWebinar,
            'registrations' => $registrations,
            'defaultSubject' => $this->defaultSubject(),
            'defaultTemplate' => $this->defaultTemplate(),
            'isMailConfigured' => $this->isMailConfigured(),
            'mailerName' => config('mail.default'),
            'mailFromAddress' => config('mail.from.address'),
        ]);
    }

    /**
     * Send webinar link via email to registrations of the selected webinar.
     */
    public function sendEmail(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $request->validate([
            'meeting_link' => ['required', 'string', 'max:255'],
            'email_subject' => ['required', 'string', 'max:255'],
            'message_template' => ['required', 'string'],
            'target' => ['required', 'in:all,pending_only,selected'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer'],
        ], [
            'meeting_link.required' => 'Link webinar wajib diisi.',
            'email_subject.required' => 'Subjek email wajib diisi.',
            'message_template.required' => 'Template pesan wajib diisi.',
        ]);

        // Simpan meeting_link pada webinar agar tersimpan permanen
        $webinar->update([
            'meeting_link' => $request->input('meeting_link'),
        ]);

        // Filter penerima berdasarkan pilihan target
        $query = $webinar->registrations();

        if ($request->input('target') === 'pending_only') {
            $query->where('email_status', 'pending');
        } elseif ($request->input('target') === 'selected') {
            $selectedIds = $request->input('selected_ids', []);
            if (empty($selectedIds)) {
                return back()->withErrors(['target' => 'Silakan pilih minimal satu pendaftar dari tabel.']);
            }
            $query->whereIn('id', $selectedIds);
        }

        $recipients = $query->get();

        if ($recipients->isEmpty()) {
            return back()->with('warning', 'Tidak ada pendaftar yang memenuhi kriteria pengiriman.');
        }

        $sentCount = 0;
        $failedCount = 0;

        $subject = $request->input('email_subject');
        $template = $request->input('message_template');
        $meetingLink = $request->input('meeting_link');

        foreach ($recipients as $recipient) {
            $result = $this->deliver($recipient, $webinar, $subject, $template, $meetingLink);
            $result['success'] ? $sentCount++ : $failedCount++;
        }

        $msg = "Pengiriman email selesai: {$sentCount} berhasil" . ($failedCount > 0 ? ", {$failedCount} gagal." : '.');

        if (! $this->isMailConfigured()) {
            $msg .= ' Catatan: MAIL_MAILER masih "' . config('mail.default') . '", jadi email hanya dicatat di log dan TIDAK benar-benar terkirim. Atur SMTP di .env untuk pengiriman sungguhan.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
            ]);
        }

        return redirect()->route('admin.webinar-registrations.index', ['webinar_id' => $webinar->id])
            ->with($failedCount > 0 ? 'warning' : 'success', $msg);
    }

    /**
     * Send the webinar link email to a single participant.
     */
    public function sendSingle(Request $request, WebinarRegistration $registration): JsonResponse|RedirectResponse
    {
        $webinar = $registration->webinar;
        $meetingLink = $webinar->meeting_link ?: '';

        $subject = $request->input('email_subject') ?: $this->defaultSubject();
        $template = $request->input('message_template') ?: $this->defaultTemplate();

        $result = $this->deliver($registration, $webinar, $subject, $template, $meetingLink);

        $message = $result['success']
            ? 'Email berhasil dikirim ke ' . $registration->email
            : 'Gagal mengirim email ke ' . $registration->name . ': ' . $result['error'];

        if ($result['success'] && ! $this->isMailConfigured()) {
            $message .= ' (mode log, tidak benar-benar terkirim)';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $message,
            ], $result['success'] ? 200 : 422);
        }

        return back()->with($result['success'] ? 'success' : 'error', $message);
    }

    /**
     * Quick toggle status for a registration (e.g. mark as sent / pending).
     */
    public function toggleStatus(Request $request, WebinarRegistration $registration): JsonResponse|RedirectResponse
    {
        $newStatus = $registration->email_status === 'sent' ? 'pending' : 'sent';
        $registration->update([
            'email_status' => $newStatus,
            'email_sent_at' => $newStatus === 'sent' ? now() : null,
            'email_error' => null,
        ]);

        $statusLabel = $newStatus === 'sent' ? 'Terkirim' : 'Pending';
        $message = "Status email untuk {$registration->name} diubah menjadi {$statusLabel}.";

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'new_status' => $newStatus,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Export participants for the selected webinar as CSV.
     */
    public function export(Webinar $webinar): StreamedResponse
    {
        $registrations = $webinar->registrations()->latest()->get();
        $safeTitle = Str::slug($webinar->title);
        $filename = "pendaftar-webinar-{$safeTitle}-" . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($registrations, $webinar) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Header info
            fputcsv($handle, ['Webinar:', $webinar->title]);
            fputcsv($handle, ['Tanggal Pelaksanaan:', $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('d F Y H:i') . ' WIB' : '-']);
            fputcsv($handle, ['Platform:', $webinar->platform ?? '-']);
            fputcsv($handle, ['Link Webinar:', $webinar->meeting_link ?: '-']);
            fputcsv($handle, []); // Empty row

            // Table headers
            fputcsv($handle, [
                'No',
                'Nama Lengkap',
                'No Telepon',
                'Email',
                'Instansi / Lembaga',
                'Pekerjaan',
                'Siapa yang Mereferensikan',
                'Motivasi Mengikuti',
                'Status Email',
                'Waktu Kirim Email',
                'Tanggal Mendaftar',
            ]);

            foreach ($registrations as $index => $item) {
                fputcsv($handle, [
                    $index + 1,
                    $item->name,
                    $item->phone ?? '-',
                    $item->email,
                    $item->institution ?? '-',
                    $item->occupation ?? '-',
                    $item->referral_source ?? '-',
                    $item->motivation ?? '-',
                    strtoupper($item->email_status),
                    $item->email_sent_at ? $item->email_sent_at->format('d/m/Y H:i:s') : '-',
                    $item->created_at->format('d/m/Y H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Kirim email link webinar ke satu pendaftar dan simpan hasilnya.
     *
     * @return array{success: bool, error?: string}
     */
    private function deliver(WebinarRegistration $recipient, Webinar $webinar, string $subject, string $template, string $meetingLink): array
    {
        if (! filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Alamat email tidak valid.';
            $recipient->update(['email_status' => 'failed', 'email_error' => $error]);

            return ['success' => false, 'error' => $error];
        }

        try {
            Mail::to($recipient->email, $recipient->name)->send(new WebinarLinkMail(
                webinar: $webinar,
                subjectLine: $this->fillPlaceholders($subject, $recipient, $webinar, $meetingLink),
                body: $this->fillPlaceholders($template, $recipient, $webinar, $meetingLink),
                meetingLink: $meetingLink,
            ));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim email link webinar', [
                'registration_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);

            $error = Str::limit($e->getMessage(), 500);
            $recipient->update(['email_status' => 'failed', 'email_error' => $error]);

            return ['success' => false, 'error' => $error];
        }

        $recipient->update([
            'email_status' => 'sent',
            'email_sent_at' => now(),
            'email_error' => null,
        ]);

        return ['success' => true];
    }

    private function fillPlaceholders(string $text, WebinarRegistration $recipient, Webinar $webinar, string $meetingLink): string
    {
        return str_replace(
            ['{nama}', '{judul_webinar}', '{tanggal}', '{waktu}', '{platform}', '{link_webinar}', '{pembicara}'],
            [
                $recipient->name,
                $webinar->title,
                $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('d F Y') : '-',
                $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('H:i') . ' WIB' : '-',
                $webinar->platform ?? 'Online Meeting',
                $meetingLink ?: '-',
                $webinar->speaker ?? '-',
            ],
            $text
        );
    }

    private function isMailConfigured(): bool
    {
        return ! in_array(config('mail.default'), self::NON_DELIVERING_MAILERS, true);
    }

    private function defaultSubject(): string
    {
        return 'Link Akses Webinar: {judul_webinar}';
    }

    private function defaultTemplate(): string
    {
        return "Halo {nama},\n\n"
            . "Terima kasih telah mendaftar webinar \"{judul_webinar}\".\n\n"
            . "Berikut informasi pelaksanaan webinar:\n"
            . "Tanggal: {tanggal}\n"
            . "Waktu: {waktu}\n"
            . "Narasumber: {pembicara}\n"
            . "Platform: {platform}\n\n"
            . "Link Webinar:\n{link_webinar}\n\n"
            . "Mohon bergabung tepat waktu. Sampai jumpa di ruang webinar!\n\n"
            . "Salam,\n"
            . "C-SHIELD Diskominfo Kota Cimahi";
    }
}
