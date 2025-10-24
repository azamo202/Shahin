<?php

namespace App\Http\Controllers\Admin\Interested;

use App\Http\Controllers\Controller;
use App\Models\Interested;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminInterestReportController extends Controller
{
    private const DEFAULT_SORT_FIELD = 'created_at';
    private const DEFAULT_SORT_DIRECTION = 'desc';

    /**
     * تقرير طلبات الاهتمام للإدارة
     * GET /api/admin/interests/report
     */
    public function report(Request $request): JsonResponse
    {
        try {
            $query = Interested::with([
                'property:id,title',
                'user:id,full_name,email,phone'
            ]);

            $this->applyFilters($query, $request);
            $this->applySearch($query, $request);
            $this->applySorting($query, $request);

            $interests = $query->get()->map(fn($item) => $this->formatInterestData($item));

            return response()->json([
                'success' => true,
                'data' => $interests,
                'count' => $interests->count(),
                'message' => 'تم جلب تقرير طلبات الاهتمام بنجاح'
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
     * خيارات الفلاتر المتاحة
     * GET /api/admin/interests/filter-options
     */
    public function filterOptions(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'status_options' => [
                        ['value' => 'قيد المراجعة', 'label' => 'قيد المراجعة'],
                        ['value' => 'تمت المراجعة', 'label' => 'تمت المراجعة'],
                        ['value' => 'تم التواصل', 'label' => 'تم التواصل'],
                        ['value' => 'ملغي', 'label' => 'ملغي'],
                    ],
                    'properties' => Property::select('id', 'title')
                        ->orderBy('title')
                        ->get()
                        ->toArray()
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

    /**
     * تطبيق الفلاتر على الاستعلام
     */
    private function applyFilters($query, Request $request): void
    {
        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب العقار
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }

    /**
     * تطبيق البحث العام
     */
    private function applySearch($query, Request $request): void
    {
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                    $userQuery->where('full_name', 'LIKE', $searchTerm)
                             ->orWhere('email', 'LIKE', $searchTerm)
                             ->orWhere('phone', 'LIKE', $searchTerm);
                })->orWhereHas('property', function ($propertyQuery) use ($searchTerm) {
                    $propertyQuery->where('title', 'LIKE', $searchTerm);
                });
            });
        }
    }

    /**
     * تطبيق الترتيب
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort_by', self::DEFAULT_SORT_FIELD);
        $sortDirection = $request->get('sort_order', self::DEFAULT_SORT_DIRECTION);

        $allowedSortFields = ['id', 'status', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        $finalSortField = in_array($sortField, $allowedSortFields) ? $sortField : self::DEFAULT_SORT_FIELD;
        $finalSortDirection = in_array($sortDirection, $allowedDirections) ? $sortDirection : self::DEFAULT_SORT_DIRECTION;

        $query->orderBy($finalSortField, $finalSortDirection);
    }

    /**
     * تنسيق بيانات طلب الاهتمام
     */
    private function formatInterestData(Interested $interest): array
    {
        return [
            'id' => $interest->id,
            'user_name' => $interest->user->full_name ?? 'غير محدد',
            'user_email' => $interest->user->email ?? 'غير متوفر',
            'user_phone' => $interest->user->phone ?? 'غير متوفر',
            'property_title' => $interest->property->title ?? 'غير محدد',
            'status' => $interest->status,
            'admin_notes' => $interest->admin_notes,
            'created_at' => $interest->created_at->format('Y-m-d H:i'),
        ];
    }
}