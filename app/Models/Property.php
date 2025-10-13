<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    // اسم الجدول (اختياري إذا استخدمنا الاسم الافتراضي "properties")
    protected $table = 'properties';

    // الحقول القابلة للتعبئة جماعياً
    protected $fillable = [
        'user_id',
        'announcement_number',
        'region',
        'city',
        'title',
        'land_type',
        'purpose',
        'geo_location_text',
        'geo_location_map',
        'total_area',
        'length_north',
        'length_south',
        'length_east',
        'length_west',
        'description',
        'deed_number',
        'images',                    // يخزن كـ JSON
        'price_per_sqm',
        'investment_duration',
        'estimated_investment_value',
        'agency_number',
        'legal_declaration',
        'status',
    ];

    // تحويل الحقول المناسبة تلقائياً
    protected $casts = [
        'images' => 'array',         // يحول JSON إلى مصفوفة تلقائياً
        'total_area' => 'float',
        'length_north' => 'float',
        'length_south' => 'float',
        'length_east' => 'float',
        'length_west' => 'float',
        'price_per_sqm' => 'float',
        'estimated_investment_value' => 'float',
    ];

    // العلاقة مع المستخدم (كل عرض ينتمي لمستخدم واحد)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
