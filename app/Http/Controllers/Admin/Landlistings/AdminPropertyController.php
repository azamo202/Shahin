<?php

namespace App\Http\Controllers\Admin\Landlistings;


use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminPropertyController extends Controller
{
    /**
     * جلب جميع الأراضي مع الفلاتر
     */
    public function getAllProperties(Request $request): JsonResponse
    {
        try {
            $properties = Property::with(['user', 'images'])
                ->withFilters($this->prepareFilters($request))
                ->latest()
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $properties,
                'message' => 'تم جلب جميع الأراضي بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأراضي: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب أرض محددة بالتفاصيل الكاملة
     */
    public function getProperty($id): JsonResponse
    {
        try {
            $property = Property::with([
                'user:id,full_name,email,phone',
                'images'
            ])->find($id);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'الارض غير موجودة'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $property,
                'message' => 'تم جلب تفاصيل الأرض بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل الأرض: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب الأراضي المقبولة
     */
    /**
     * جلب الأراضي المفتوحة (كانت مقبولة سابقًا)
     */
    public function getOpenProperties(Request $request): JsonResponse
    {
        try {
            $properties = Property::with(['user', 'images'])
                ->withStatus('مفتوح')   // ✅ بدل مقبول
                ->withFilters($this->prepareFilters($request))
                ->latest()
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $properties,
                'message' => 'تم جلب الأراضي المفتوحة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأراضي المفتوحة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب الأراضي المرفوضة
     */
    public function getRejectedProperties(Request $request): JsonResponse
    {
        try {
            $properties = Property::with(['user', 'images'])
                ->withStatus('مرفوض')
                ->withFilters($this->prepareFilters($request))
                ->latest()
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $properties,
                'message' => 'تم جلب الأراضي المرفوضة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأراضي المرفوضة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب الأراضي قيد المعالجة
     */
    public function getPendingProperties(Request $request): JsonResponse
    {
        try {
            $properties = Property::with(['user', 'images'])
                ->withStatus('قيد المراجعة')
                ->withFilters($this->prepareFilters($request))
                ->latest()
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $properties,
                'message' => 'تم جلب الأراضي قيد المعالجة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الأراضي قيد المعالجة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إعداد الفلاتر من الطلب
     */
    private function prepareFilters(Request $request): array
    {
        $filters = [];

        // فلتر المنطقة
        if ($request->has('region') && !empty($request->region)) {
            $filters['region'] = $request->region;
        }

        // فلتر المدينة
        if ($request->has('city') && !empty($request->city)) {
            $filters['city'] = $request->city;
        }

        // فلتر سعر المتر
        if ($request->has('min_price') && !empty($request->min_price)) {
            $filters['min_price'] = $request->min_price;
        }
        if ($request->has('max_price') && !empty($request->max_price)) {
            $filters['max_price'] = $request->max_price;
        }

        // فلتر سعر الاستثمار
        if ($request->has('min_investment') && !empty($request->min_investment)) {
            $filters['min_investment'] = $request->min_investment;
        }
        if ($request->has('max_investment') && !empty($request->max_investment)) {
            $filters['max_investment'] = $request->max_investment;
        }

        return $filters;
    }

    /**
     * الحصول على إحصائيات الأراضي
     */
    public function getPropertiesStats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Property::count(),
                'accepted' => Property::accepted()->count(),
                'rejected' => Property::withStatus('مرفوض')->count(),
                'pending' => Property::withStatus('قيد المراجعة')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'تم جلب إحصائيات الأراضي بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات: ' . $e->getMessage()
            ], 500);
        }
    }
}
