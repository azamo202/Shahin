<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    // الحقول القابلة للتعيين جماعياً
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
        'cover_image', // حقل الغلاف الجديد
    ];

    /**
     * ربط المزاد بالمستخدم الذي أنشأه
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * ربط المزاد بالصور
     */
    public function images()
    {
        return $this->hasMany(AuctionImage::class);
    }

    /**
     * ربط المزاد بالفيديوهات
     */
    public function videos()
    {
        return $this->hasMany(AuctionVideo::class);
    }
}
