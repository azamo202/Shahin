<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PublicPropertyController extends Controller
{
    /** جلب العقارات للواجهة العامة (الزوار) مع الفلترة */
    public function index(Request $request)
    {
        $query = Property::whereIn('status', ['مفتوح', 'تم البيع'])->with('images');

        // تطبيق الفلاتر
        $query = $this->applyFilters($query, $request);

        // الترتيب
        $query = $this->applySorting($query, $request);

        $properties = $query->get();

        return response()->json([
            'status' => true,
            'data' => $properties,
            'filters_applied' => $this->getAppliedFilters($request),
            'message' => 'تم جلب العقارات بنجاح'
        ]);
    }

    /** عرض عقار محدد للواجهة العامة */
    public function show($id)
    {
        $property = Property::whereIn('status', ['مفتوح', 'تم البيع'])->with('images')->find($id);

        if (!$property) {
            return response()->json([
                'status' => false,
                'message' => 'العقار غير موجود'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $property,
            'message' => 'تم جلب بيانات العقار بنجاح'
        ]);
    }

    /** تطبيق الفلاتر */
    private function applyFilters($query, Request $request)
    {
        // فلترة حسب المنطقة
        if ($request->has('region') && $request->region) {
            $query->where('region', 'like', '%' . $request->region . '%');
        }

        // فلترة حسب المدينة
        if ($request->has('city') && $request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // فلترة حسب نوع الأرض
        if ($request->has('land_type') && $request->land_type) {
            $query->where('land_type', $request->land_type);
        }

        // فلترة حسب الغرض
        if ($request->has('purpose') && $request->purpose) {
            $query->where('purpose', $request->purpose);
        }

        // فلترة حسب المساحة الكلية
        if ($request->has('min_area') && $request->min_area) {
            $query->where('total_area', '>=', $request->min_area);
        }
        if ($request->has('max_area') && $request->max_area) {
            $query->where('total_area', '<=', $request->max_area);
        }

        // فلترة حسب السعر للمتر (للبيع)
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price_per_sqm', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price_per_sqm', '<=', $request->max_price);
        }

        // فلترة حسب قيمة الاستثمار (للإستثمار)
        if ($request->has('min_investment') && $request->min_investment) {
            $query->where('estimated_investment_value', '>=', $request->min_investment);
        }
        if ($request->has('max_investment') && $request->max_investment) {
            $query->where('estimated_investment_value', '<=', $request->max_investment);
        }

        // البحث في العنوان والوصف
        if ($request->has('search') && $request->search) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('region', 'like', $searchTerm)
                  ->orWhere('city', 'like', $searchTerm);
            });
        }

        return $query;
    }

    /** تطبيق الترتيب */
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = [
            'created_at', 'total_area', 'price_per_sqm', 
            'estimated_investment_value', 'investment_duration'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    /** الحصول على الفلاتر المطبقة */
    private function getAppliedFilters(Request $request)
    {
        $filters = [];
        $filterFields = [
            'region', 'city', 'land_type', 'purpose', 
            'min_area', 'max_area', 'min_price', 'max_price',
            'min_investment', 'max_investment', 'max_duration',
            'date_from', 'date_to', 'search', 'sort_by', 'sort_order'
        ];

        foreach ($filterFields as $field) {
            if ($request->has($field) && $request->$field) {
                $filters[$field] = $request->$field;
            }
        }

        return $filters;
    }

    /** جلب الخيارات المتاحة للفلاتر */
    public function getFilterOptions()
    {
        $options = [
            'regions' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->distinct()->pluck('region')->filter()->values(),
            'cities' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->distinct()->pluck('city')->filter()->values(),
            'land_types' => ['سكني', 'تجاري', 'زراعي'],
            'purposes' => ['بيع', 'استثمار'],
            'price_ranges' => [
                'min' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->min('price_per_sqm'),
                'max' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->max('price_per_sqm')
            ],
            'area_ranges' => [
                'min' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->min('total_area'),
                'max' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->max('total_area')
            ],
            'investment_ranges' => [
                'min' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->min('estimated_investment_value'),
                'max' => Property::whereIn('status', ['مفتوح', 'تم البيع'])->max('estimated_investment_value')
            ]
        ];

        return response()->json([
            'status' => true,
            'data' => $options,
            'message' => 'تم جلب خيارات الفلترة بنجاح'
        ]);
    }
}
