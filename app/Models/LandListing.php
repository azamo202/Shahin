<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'land_type',
        'location',
        'area',
        'description',
        'deed_image',
        'purpose',
        'price_per_meter',
        'investment_start',
        'investment_end',
        'investment_estimated_value',
        'real_estate_announcement_no',
        'no_dispute_confirmed',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'price_per_meter' => 'decimal:2',
        'investment_estimated_value' => 'decimal:2',
        'no_dispute_confirmed' => 'boolean',
        'investment_start' => 'datetime:Y-m-d', // صيغة عرض التاريخ
        'investment_end' => 'datetime:Y-m-d',
    ];

    /**
     * علاقة الربط مع المستخدم (المالك)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope للتصفية حسب نوع الأرض
     */
    public function scopeOfLandType($query, $type)
    {
        return $query->where('land_type', $type);
    }

    /**
     * Scope للتصفية حسب الغرض
     */
    public function scopeOfPurpose($query, $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    /**
     * Scope للتصفية حسب الموقع
     */
    public function scopeOfLocation($query, $location)
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    /**
     * Scope للتصفية حسب نطاق المساحة
     */
    public function scopeOfAreaRange($query, $minArea, $maxArea)
    {
        if ($minArea) {
            $query->where('area', '>=', $minArea);
        }
        if ($maxArea) {
            $query->where('area', '<=', $maxArea);
        }
        return $query;
    }
}
