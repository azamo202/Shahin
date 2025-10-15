<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

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
        'cover_image',
        'price_per_sqm',
        'investment_duration',
        'estimated_investment_value',
        'agency_number',
        'legal_declaration',
        'status'
    ];

    protected $casts = [
        'total_area' => 'decimal:2',
        'length_north' => 'decimal:2',
        'length_south' => 'decimal:2',
        'length_east' => 'decimal:2',
        'length_west' => 'decimal:2',
        'price_per_sqm' => 'decimal:2',
        'estimated_investment_value' => 'decimal:2',
        'investment_duration' => 'integer',
        'images' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * علاقة الربط مع المستخدم (المالك)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة الصور الإضافية
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * Scope للعقارات المقبولة فقط
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'مقبول');
    }

    /**
     * Scope للعقارات الخاصة بمستخدم معين
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope للتصفية حسب الحالة
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope للتصفية حسب المنطقة
     */
    public function scopeInRegion($query, $region)
    {
        return $query->where('region', 'like', '%' . $region . '%');
    }

    /**
     * Scope للتصفية حسب المدينة
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
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
     * Scope للتصفية حسب نطاق المساحة
     */
    public function scopeOfAreaRange($query, $minArea = null, $maxArea = null)
    {
        if ($minArea) {
            $query->where('total_area', '>=', $minArea);
        }
        if ($maxArea) {
            $query->where('total_area', '<=', $maxArea);
        }
        return $query;
    }

    /**
     * Scope للتصفية حسب نطاق السعر للمتر
     */
    public function scopeOfPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('price_per_sqm', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('price_per_sqm', '<=', $maxPrice);
        }
        return $query;
    }

    /**
     * Scope للتصفية حسب نطاق قيمة الاستثمار
     */
    public function scopeOfInvestmentRange($query, $minInvestment = null, $maxInvestment = null)
    {
        if ($minInvestment) {
            $query->where('estimated_investment_value', '>=', $minInvestment);
        }
        if ($maxInvestment) {
            $query->where('estimated_investment_value', '<=', $maxInvestment);
        }
        return $query;
    }


    /**
     * Scope للبحث في العنوان والوصف والمنطقة والمدينة
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'like', '%' . $searchTerm . '%')
              ->orWhere('description', 'like', '%' . $searchTerm . '%')
              ->orWhere('region', 'like', '%' . $searchTerm . '%')
              ->orWhere('city', 'like', '%' . $searchTerm . '%');
        });
    }
       /**
     * Scope شامل لجميع الفلاتر
     */
    public function scopeWithFilters($query, array $filters = [])
    {
        return $query
            ->when(isset($filters['region']) && $filters['region'], function ($q) use ($filters) {
                $q->inRegion($filters['region']);
            })
            ->when(isset($filters['city']) && $filters['city'], function ($q) use ($filters) {
                $q->inCity($filters['city']);
            })
            ->when(isset($filters['land_type']) && $filters['land_type'], function ($q) use ($filters) {
                $q->ofLandType($filters['land_type']);
            })
            ->when(isset($filters['purpose']) && $filters['purpose'], function ($q) use ($filters) {
                $q->ofPurpose($filters['purpose']);
            })
            ->when(isset($filters['min_area']) || isset($filters['max_area']), function ($q) use ($filters) {
                $q->ofAreaRange($filters['min_area'] ?? null, $filters['max_area'] ?? null);
            })
            ->when(isset($filters['min_price']) || isset($filters['max_price']), function ($q) use ($filters) {
                $q->ofPriceRange($filters['min_price'] ?? null, $filters['max_price'] ?? null);
            })
            ->when(isset($filters['min_investment']) || isset($filters['max_investment']), function ($q) use ($filters) {
                $q->ofInvestmentRange($filters['min_investment'] ?? null, $filters['max_investment'] ?? null);
            })
            ->when(isset($filters['max_duration']) && $filters['max_duration'], function ($q) use ($filters) {
                $q->ofMaxDuration($filters['max_duration']);
            })
            ->when(isset($filters['search']) && $filters['search'], function ($q) use ($filters) {
                $q->search($filters['search']);
            })
            ->when(isset($filters['date_from']) || isset($filters['date_to']), function ($q) use ($filters) {
                $q->ofDateRange($filters['date_from'] ?? null, $filters['date_to'] ?? null);
            });
    }

    /**
     * الحصول على رابط صورة الغلاف
     */
    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    /**
     * الحصول على روابط الصور الإضافية
     */
    public function getImagesUrlsAttribute()
    {
        if (!$this->images) {
            return [];
        }

        return $this->images->map(function ($image) {
            return asset('storage/' . $image->image_path);
        });
    }

    /**
     * التحقق إذا كان العقار للبيع
     */
    public function getIsForSaleAttribute()
    {
        return $this->purpose === 'بيع';
    }

    /**
     * التحقق إذا كان العقار للاستثمار
     */
    public function getIsForInvestmentAttribute()
    {
        return $this->purpose === 'استثمار';
    }

    /**
     * حساب السعر الإجمالي (للعقارات المعروضة للبيع)
     */
    public function getTotalPriceAttribute()
    {
        if ($this->is_for_sale && $this->price_per_sqm && $this->total_area) {
            return $this->price_per_sqm * $this->total_area;
        }
        return null;
    }
}