<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudService
{
    protected ?string $token;
    protected ?string $phoneNumberId;
    protected string $apiVersion;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token') ?: env('WHATSAPP_TOKEN');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id') ?: env('WHATSAPP_PHONE_NUMBER_ID');
        $this->apiVersion = config('services.whatsapp.api_version') ?: env('WHATSAPP_API_VERSION', 'v19.0');
    }

    /**
     * Check if WhatsApp Business Cloud API credentials are fully configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->token) && ! empty($this->phoneNumberId);
    }

    /**
     * Format phone number to international WhatsApp standard without '+' (e.g., 628123456789).
     */
    public function formatPhoneNumber(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $cleaned = preg_replace('/\D/', '', $phone);

        if (empty($cleaned)) {
            return null;
        }

        // Convert Indonesian domestic format 08... to 628...
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Send a text message via WhatsApp Business Cloud API.
     *
     * @param string $toPhone Recipient phone number
     * @param string $message Text message body
     * @return array [success: bool, simulated: bool, message_id?: string, error?: string]
     */
    public function sendMessage(string $toPhone, string $message): array
    {
        $formattedPhone = $this->formatPhoneNumber($toPhone);

        if (! $formattedPhone) {
            return [
                'success' => false,
                'simulated' => false,
                'error' => 'Nomor telepon tidak valid.',
            ];
        }

        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'simulated' => true,
                'error' => 'WhatsApp Cloud API belum dikonfigurasi di .env (WHATSAPP_TOKEN & WHATSAPP_PHONE_NUMBER_ID).',
            ];
        }

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $formattedPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => true,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;

                return [
                    'success' => true,
                    'simulated' => false,
                    'message_id' => $messageId,
                ];
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? $response->body();

            Log::warning('WhatsApp Cloud API error', [
                'phone' => $formattedPhone,
                'response' => $errorData,
            ]);

            return [
                'success' => false,
                'simulated' => false,
                'error' => $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp Cloud API exception', [
                'phone' => $formattedPhone,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'simulated' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
