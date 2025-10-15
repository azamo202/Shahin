<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'video_path',
    ];

    /**
     * ربط الفيديو بالمزاد
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
