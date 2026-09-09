<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlyerImage extends Model
{
    protected $fillable = [
        'flyer_id',
        'image',
        'sort_order',
    ];

    public function flyer()
    {
        return $this->belongsTo(Flyer::class);
    }
}
