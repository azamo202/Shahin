<?php

namespace App\Http\Controllers\Admin\landlistings;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPropertyReportController extends Controller
{
    private const DEFAULT_SORT_FIELD = 'created_at';
    private const DEFAULT_SORT_DIRECTION = 'desc';
    private const ALL_STATUS = 'all';

    /**
     * تقرير العقارات للإدارة
     * GET /api/admin/properties/report
     */
    public function report(Request $request): JsonResponse
    {
        try {
            $query = Property::with(['user:id,full_name,email,phone', 'images']);

            $this->applyFilters($query, $request);
            $this->applySorting($query, $request);

            $properties = $query->get()->map(fn($property) => $this->formatPropertyData($property));

            return response()->json([
                'success' => true,
                'data' => $properties,
                'count' => $properties->count(),
                'message' => 'تم جلب تقرير العقارات بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب التقرير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تطبيق الفلاتر على الاستعلام
     */
    private function applyFilters($query, Request $request): void
    {
        // فلترة حسب الحالة
        if ($request->filled('status') && $request->status != self::ALL_STATUS) {
            $query->where('status', $request->status);
        }

        // فلترة حسب المنطقة
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        // فلترة حسب المدينة
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // فلترة حسب سعر المتر
        if ($request->filled('min_price')) {
            $query->where('price_per_meter', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_meter', '<=', $request->max_price);
        }

        // فلترة حسب سعر الاستثمار
        if ($request->filled('min_investment')) {
            $query->where('investment_price', '>=', $request->min_investment);
        }
        if ($request->filled('max_investment')) {
            $query->where('investment_price', '<=', $request->max_investment);
        }
    }

    /**
     * تطبيق الترتيب على الاستعلام
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort_field', self::DEFAULT_SORT_FIELD);
        $sortDirection = $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION);

        $allowedSortFields = ['id', 'title', 'status', 'region', 'city', 'price_per_meter', 'investment_price', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        $finalSortField = in_array($sortField, $allowedSortFields) ? $sortField : self::DEFAULT_SORT_FIELD;
        $finalSortDirection = in_array($sortDirection, $allowedDirections) ? $sortDirection : self::DEFAULT_SORT_DIRECTION;

        $query->orderBy($finalSortField, $finalSortDirection);
    }

    /**
     * تنسيق بيانات العقار للتقرير
     */
    private function formatPropertyData(Property $property): array
    {
        return [
            'id' => $property->id,
            'title' => $property->title ?? 'غير محدد',
            'status' => $property->status,
            'region' => $property->region,
            'city' => $property->city,
            'price_per_meter' => $property->price_per_meter,
            'investment_price' => $property->investment_price,
            'user' => [
                'id' => $property->user->id ?? null,
                'full_name' => $property->user->full_name ?? 'غير محدد',
                'email' => $property->user->email ?? 'غير متوفر',
                'phone' => $property->user->phone ?? 'غير متوفر',
            ],
            'images' => $property->images->pluck('url')->toArray(),
            'created_at' => $property->created_at->format('Y-m-d H:i'),
        ];
    }


    /**
     * خيارات الفلاتر المتاحة
     * GET /api/admin/properties/filter-options
     */
    public function filterOptions(): JsonResponse
    {
        try {
            $regions = Property::select('region')
                ->distinct()
                ->whereNotNull('region')
                ->orderBy('region')
                ->pluck('region')
                ->toArray();

            $cities = Property::select('city')
                ->distinct()
                ->whereNotNull('city')
                ->orderBy('city')
                ->pluck('city')
                ->toArray();

            $statusOptions = [
                ['value' => 'مفتوح', 'label' => 'مفتوح'],
                ['value' => 'مرفوض', 'label' => 'مرفوض'],
                ['value' => 'قيد المراجعة', 'label' => 'قيد المراجعة'],
                ['value' => 'تم البيع', 'label' => 'تم البيع'],
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'regions' => $regions,
                    'cities' => $cities,
                    'status_options' => $statusOptions,
                ],
                'message' => 'تم جلب خيارات الفلاتر بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب خيارات الفلاتر',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}