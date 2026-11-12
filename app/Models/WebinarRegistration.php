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
        'email_status',
        'email_sent_at',
        'email_error',
    ];

    protected function casts(): array
    {
        return [
            'email_sent_at' => 'datetime',
        ];
    }

    public function webinar(): BelongsTo
    {
        return $this->belongsTo(Webinar::class);
    }
}
