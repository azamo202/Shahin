<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'announcement_number', 'region', 'city', 'title', 'land_type',
        'purpose', 'geo_location_text', 'geo_location_map', 'total_area',
        'length_north', 'length_south', 'length_east', 'length_west',
        'description', 'deed_number', 'images', 'price_per_sqm', 'investment_duration',
        'estimated_investment_value', 'agency_number', 'legal_declaration', 'status'
    ];

    protected $casts = [
        'images' => 'array'
    ];

    // Scope لجلب العقارات الخاصة بالمستخدم
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope للعقارات المقبولة
    public function scopeAccepted($query)
    {
        return $query->where('status', 'مقبول');
    }

    // Scope حسب الحالة
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
