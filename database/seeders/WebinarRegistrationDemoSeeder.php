<?php

namespace Database\Seeders;

use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Database\Seeder;

class WebinarRegistrationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $webinars = Webinar::all();

        if ($webinars->isEmpty()) {
            return;
        }

        $participantsData = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@example.com',
                'phone' => '081234567890',
                'institution' => 'Bappeda Kota Cimahi',
                'occupation' => 'PNS / ASN',
                'motivation' => 'Ingin memahami perlindungan data dan keamanan informasi di kantor.',
                'referral_source' => 'Rekan Kerja Diskominfo',
                'whatsapp_status' => 'sent',
                'whatsapp_sent_at' => now()->subHours(5),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'phone' => '082198765432',
                'institution' => 'Universitas Jenderal Achmad Yani (UNJANI)',
                'occupation' => 'Mahasiswa',
                'motivation' => 'Menambah wawasan seputar cyber security untuk tugas akhir.',
                'referral_source' => 'Dosen Pembimbing',
                'whatsapp_status' => 'pending',
                'whatsapp_sent_at' => null,
            ],
            [
                'name' => 'Budi Pratama',
                'email' => 'budi.pratama@example.com',
                'phone' => '085712348765',
                'institution' => 'Kelurahan Pasirkaliki',
                'occupation' => 'Staf Kelurahan',
                'motivation' => 'Mencegah penipuan phising dan ransomware pada komputer pelayanan.',
                'referral_source' => 'Instagram @diskominfocimahi',
                'whatsapp_status' => 'sent',
                'whatsapp_sent_at' => now()->subHours(2),
            ],
            [
                'name' => 'Dewi Anggraeni',
                'email' => 'dewi.anggraeni@example.com',
                'phone' => '087856781234',
                'institution' => 'SMK Negeri 1 Cimahi',
                'occupation' => 'Guru Informatika',
                'motivation' => 'Ingin mengajarkan literasi keamanan digital kepada murid-murid.',
                'referral_source' => 'Grup WhatsApp Guru Cimahi',
                'whatsapp_status' => 'pending',
                'whatsapp_sent_at' => null,
            ],
            [
                'name' => 'Rian Hidayat',
                'email' => 'rian.hidayat@example.com',
                'phone' => '089611223344',
                'institution' => 'PT Cimahi Digital Solusi',
                'occupation' => 'Web Developer',
                'motivation' => 'Mempelajari best practice keamanan web aplikasi.',
                'referral_source' => null, // Mandiri / Tidak tersedia
                'whatsapp_status' => 'pending',
                'whatsapp_sent_at' => null,
            ],
            [
                'name' => 'Putri Ayu Wandira',
                'email' => 'putri.ayu@example.com',
                'phone' => '081377889900',
                'institution' => 'Kecamatan Cimahi Tengah',
                'occupation' => 'Tenaga Honorer',
                'motivation' => 'Meningkatkan kesadaran keamanan digital.',
                'referral_source' => 'Teman Sekantor',
                'whatsapp_status' => 'sent',
                'whatsapp_sent_at' => now()->subDay(),
            ],
        ];

        // Seed participants to all webinars
        foreach ($webinars as $wIndex => $webinar) {
            // Set default meeting link if not set
            if (! $webinar->meeting_link) {
                $webinar->update([
                    'meeting_link' => 'https://zoom.us/j/9876543210' . ($wIndex + 1) . '?pwd=CShieldWebinar' . ($wIndex + 1),
                ]);
            }

            foreach ($participantsData as $pIndex => $p) {
                // Vary email per webinar so unique constraint is satisfied
                $uniqueEmail = str_replace('@', "+w{$webinar->id}@example.com", $p['email']);

                WebinarRegistration::firstOrCreate(
                    [
                        'webinar_id' => $webinar->id,
                        'email' => $uniqueEmail,
                    ],
                    [
                        'name' => $p['name'],
                        'phone' => $p['phone'],
                        'institution' => $p['institution'],
                        'occupation' => $p['occupation'],
                        'motivation' => $p['motivation'],
                        'referral_source' => $p['referral_source'],
                        'whatsapp_status' => ($wIndex + $pIndex) % 2 === 0 ? 'sent' : 'pending',
                        'whatsapp_sent_at' => ($wIndex + $pIndex) % 2 === 0 ? now()->subHours($pIndex + 1) : null,
                    ]
                );
            }
        }
    }
}
