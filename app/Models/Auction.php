<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'intro_link',
        'start_time',
        'auction_date',
        'address',
        'latitude',
        'longitude',
        'status',
        'cover_image',
        'rejection_reason',
    ];

    protected $casts = [
        'auction_date' => 'date',
    ];

    // النطاق للمزادات المفتوحة فقط
    public function scopeOpen($query)
    {
        return $query->where('status', 'مفتوح');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(AuctionImage::class);
    }

    public function videos()
    {
        return $this->hasMany(AuctionVideo::class);
    }
  
    public function company()
    {
        return $this->belongsTo(AuctionCompany::class, 'user_id', 'user_id');
    }
}
