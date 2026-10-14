<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebinarRegistration extends Model
{
    protected $fillable = [
        'webinar_id',
        'name',
        'email',
        'phone',
        'institution',
        'occupation',
        'motivation',
        'referral_source',
        'whatsapp_status',
        'whatsapp_sent_at',
        'whatsapp_error',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_sent_at' => 'datetime',
        ];
    }

    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class);
    }

    /**
     * Get phone number formatted for international WhatsApp (e.g. 628123456789).
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        // Keep only digits
        $cleaned = preg_replace('/\D/', '', $this->phone);

        if (empty($cleaned)) {
            return null;
        }

        // Convert leading 0 to 62 (Indonesia country code)
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Generate direct WhatsApp click-to-chat URL.
     */
    public function getWhatsappDirectUrlAttribute(): ?string
    {
        $phone = $this->formatted_phone;
        if (! $phone) {
            return null;
        }

        return 'https://wa.me/' . $phone;
    }
}
