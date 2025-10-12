<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'user_id', 'title', 'auction_type', 'description',
        'start_date', 'end_date', 'video_url', 'images',
        'info_link', 'location'
    ];

    protected $casts = [
        'images' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

