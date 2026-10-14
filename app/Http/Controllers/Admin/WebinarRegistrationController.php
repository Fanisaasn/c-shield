<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Services\WhatsAppCloudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebinarRegistrationController extends Controller
{
    /**
     * Display the webinar registrations management page grouped by webinar.
     */
    public function index(Request $request, WhatsAppCloudService $waService): View
    {
        // 1. Kelompokkan pendaftar berdasarkan judul webinar
        $webinars = Webinar::query()
            ->withCount([
                'registrations',
                'registrations as pending_count' => fn ($q) => $q->where('whatsapp_status', 'pending'),
                'registrations as sent_count' => fn ($q) => $q->where('whatsapp_status', 'sent'),
                'registrations as failed_count' => fn ($q) => $q->where('whatsapp_status', 'failed'),
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

            // Filter status WhatsApp
            if ($request->filled('status') && in_array($request->query('status'), ['pending', 'sent', 'failed'])) {
                $query->where('whatsapp_status', $request->query('status'));
            }

            $registrations = $query->orderByDesc('id')->get();
        }

        // Default template pesan WhatsApp
        $defaultTemplate = "Halo {nama}, terima kasih telah mendaftar webinar *{judul_webinar}*.\n\n"
            . "Berikut informasi akses pelaksanaan webinar:\n"
            . "🗓 Tanggal: {tanggal}\n"
            . "⏰ Waktu: {waktu}\n"
            . "🎤 Narasumber: {pembicara}\n"
            . "💻 Platform: {platform}\n\n"
            . "🔗 Link Webinar:\n{link_webinar}\n\n"
            . "Mohon bergabung tepat waktu ya. Sampai jumpa di ruang webinar!\n\n"
            . "_C-SHIELD Diskominfo Kota Cimahi_";

        $isCloudApiConfigured = $waService->isConfigured();

        return view('admin.webinar-registrations.index', [
            'webinars' => $webinars,
            'selectedWebinar' => $selectedWebinar,
            'registrations' => $registrations,
            'defaultTemplate' => $defaultTemplate,
            'isCloudApiConfigured' => $isCloudApiConfigured,
        ]);
    }

    /**
     * Send webinar link via WhatsApp to registrations of the selected webinar.
     */
    public function sendWhatsApp(Request $request, Webinar $webinar, WhatsAppCloudService $waService): RedirectResponse|JsonResponse
    {
        $request->validate([
            'meeting_link' => ['required', 'string', 'max:255'],
            'message_template' => ['required', 'string'],
            'target' => ['required', 'in:all,pending_only,selected'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer'],
        ], [
            'meeting_link.required' => 'Link webinar wajib diisi.',
            'message_template.required' => 'Template pesan wajib diisi.',
        ]);

        // Simpan meeting_link pada webinar agar tersimpan permanen
        $webinar->update([
            'meeting_link' => $request->input('meeting_link'),
        ]);

        // Filter penerima berdasarkan pilihan target
        $query = $webinar->registrations();

        if ($request->input('target') === 'pending_only') {
            $query->where('whatsapp_status', 'pending');
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
        $simulatedCount = 0;

        $template = $request->input('message_template');
        $meetingLink = $request->input('meeting_link');
        $isConfigured = $waService->isConfigured();

        foreach ($recipients as $recipient) {
            if (! $recipient->phone) {
                $recipient->update([
                    'whatsapp_status' => 'failed',
                    'whatsapp_error' => 'Nomor WhatsApp tidak tersedia.',
                ]);
                $failedCount++;
                continue;
            }

            // Ganti tag / variabel pada template pesan
            $personalizedMessage = str_replace(
                [
                    '{nama}',
                    '{judul_webinar}',
                    '{tanggal}',
                    '{waktu}',
                    '{platform}',
                    '{link_webinar}',
                    '{pembicara}',
                ],
                [
                    $recipient->name,
                    $webinar->title,
                    $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('d F Y') : '-',
                    $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('H:i') . ' WIB' : '-',
                    $webinar->platform ?? 'Online Meeting',
                    $meetingLink,
                    $webinar->speaker ?? '-',
                ],
                $template
            );

            if ($isConfigured) {
                // Kirim via WhatsApp Business Cloud API Meta
                $result = $waService->sendMessage($recipient->phone, $personalizedMessage);

                if ($result['success']) {
                    $recipient->update([
                        'whatsapp_status' => 'sent',
                        'whatsapp_sent_at' => now(),
                        'whatsapp_error' => null,
                    ]);
                    $sentCount++;
                } else {
                    $recipient->update([
                        'whatsapp_status' => 'failed',
                        'whatsapp_error' => $result['error'] ?? 'Pengiriman gagal',
                    ]);
                    $failedCount++;
                }
            } else {
                // Mode simulasi jika WHATSAPP_TOKEN & WHATSAPP_PHONE_NUMBER_ID belum diisi
                $recipient->update([
                    'whatsapp_status' => 'sent',
                    'whatsapp_sent_at' => now(),
                    'whatsapp_error' => null,
                ]);
                $simulatedCount++;
            }
        }

        if ($isConfigured) {
            $msg = "Pengiriman WhatsApp selesai: {$sentCount} berhasil";
            if ($failedCount > 0) {
                $msg .= ", {$failedCount} gagal.";
            } else {
                $msg .= ".";
            }
        } else {
            $msg = "Proses broadcast selesai ({$simulatedCount} pesan diproses). Catatan: WhatsApp Cloud API berjalan dalam mode simulasi karena WHATSAPP_TOKEN belum diisi di .env. Anda juga dapat menggunakan tombol WhatsApp Web untuk kirim langsung.";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'sent_count' => $sentCount + $simulatedCount,
                'failed_count' => $failedCount,
            ]);
        }

        return redirect()->route('admin.webinar-registrations.index', ['webinar_id' => $webinar->id])
            ->with('success', $msg);
    }

    /**
     * Send WhatsApp link to a single participant.
     */
    public function sendSingle(Request $request, WebinarRegistration $registration, WhatsAppCloudService $waService): JsonResponse|RedirectResponse
    {
        $webinar = $registration->webinar;
        $meetingLink = $webinar->meeting_link ?: $webinar->registration_url ?: 'https://zoom.us';

        $template = $request->input('message_template');
        if (! $template) {
            $template = "Halo {nama}, berikut link akses webinar *{judul_webinar}*:\n\n🔗 {link_webinar}\n\nSampai jumpa di webinar!";
        }

        $message = str_replace(
            [
                '{nama}',
                '{judul_webinar}',
                '{tanggal}',
                '{waktu}',
                '{platform}',
                '{link_webinar}',
                '{pembicara}',
            ],
            [
                $registration->name,
                $webinar->title,
                $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('d F Y') : '-',
                $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('H:i') . ' WIB' : '-',
                $webinar->platform ?? 'Online Meeting',
                $meetingLink,
                $webinar->speaker ?? '-',
            ],
            $template
        );

        if (! $registration->phone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WhatsApp tidak tersedia.',
            ], 422);
        }

        if ($waService->isConfigured()) {
            $result = $waService->sendMessage($registration->phone, $message);

            if ($result['success']) {
                $registration->update([
                    'whatsapp_status' => 'sent',
                    'whatsapp_sent_at' => now(),
                    'whatsapp_error' => null,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pesan WhatsApp berhasil dikirim ke ' . $registration->name,
                ]);
            }

            $registration->update([
                'whatsapp_status' => 'failed',
                'whatsapp_error' => $result['error'] ?? 'Pengiriman gagal',
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim pesan: ' . ($result['error'] ?? 'Terjadi kesalahan.'),
            ], 500);
        }

        // Simulated success
        $registration->update([
            'whatsapp_status' => 'sent',
            'whatsapp_sent_at' => now(),
            'whatsapp_error' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan berhasil diproses (Simulasi Cloud API).',
        ]);
    }

    /**
     * Quick toggle status for a registration (e.g. mark as sent / pending).
     */
    public function toggleStatus(Request $request, WebinarRegistration $registration): JsonResponse|RedirectResponse
    {
        $newStatus = $registration->whatsapp_status === 'sent' ? 'pending' : 'sent';
        $registration->update([
            'whatsapp_status' => $newStatus,
            'whatsapp_sent_at' => $newStatus === 'sent' ? now() : null,
            'whatsapp_error' => null,
        ]);

        $statusLabel = $newStatus === 'sent' ? 'Terkirim' : 'Pending';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'new_status' => $newStatus,
                'message' => "Status WhatsApp untuk {$registration->name} diubah menjadi {$statusLabel}.",
            ]);
        }

        return back()->with('success', "Status WhatsApp untuk {$registration->name} diubah menjadi {$statusLabel}.");
    }

    /**
     * Export participants for the selected webinar as CSV.
     */
    public function export(Webinar $webinar): StreamedResponse
    {
        $registrations = $webinar->registrations()->latest()->get();
        $safeTitle = \Illuminate\Support\Str::slug($webinar->title);
        $filename = "pendaftar-webinar-{$safeTitle}-" . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($registrations, $webinar) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Header info
            fputcsv($handle, ['Webinar:', $webinar->title]);
            fputcsv($handle, ['Tanggal Pelaksanaan:', $webinar->webinar_date ? $webinar->webinar_date->translatedFormat('d F Y H:i') . ' WIB' : '-']);
            fputcsv($handle, ['Platform:', $webinar->platform ?? '-']);
            fputcsv($handle, ['Link Webinar:', $webinar->meeting_link ?? $webinar->registration_url ?? '-']);
            fputcsv($handle, []); // Empty row

            // Table headers
            fputcsv($handle, [
                'No',
                'Nama Lengkap',
                'No WhatsApp / Telepon',
                'Email',
                'Instansi / Lembaga',
                'Pekerjaan',
                'Siapa yang Mereferensikan',
                'Motivasi Mengikuti',
                'Status WhatsApp',
                'Waktu Kirim WA',
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
                    strtoupper($item->whatsapp_status),
                    $item->whatsapp_sent_at ? $item->whatsapp_sent_at->format('d/m/Y H:i:s') : '-',
                    $item->created_at->format('d/m/Y H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
