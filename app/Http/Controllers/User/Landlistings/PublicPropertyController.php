<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Pagination\LengthAwarePaginator;

class PublicPropertyController extends Controller
{
    /** جلب آخر العقارات مع الفلاتر */
    public function latestProperties(Request $request): JsonResponse
    {
        return $this->handlePropertyRequest($request, function ($query) use ($request) {
            $limit = $request->get('limit', 7);
            return $query->take($limit)->get();
        }, "آخر العقارات");
    }

    /** جلب العقارات للواجهة العامة مع الفلاتر ودعم Pagination */
    public function index(Request $request): JsonResponse
    {
        return $this->handlePropertyRequest($request, function ($query) use ($request) {
            $perPage = $request->get('per_page', 12);
            return $query->paginate($perPage);
        }, "العقارات");
    }

    /** عرض عقار محدد */
    public function show($id): JsonResponse
    {
        $property = Property::whereIn('status', ['مفتوح', 'تم البيع'])
            ->with('images')
            ->find($id);

        if (!$property) {
            return $this->jsonResponse(false, [], 'العقار غير موجود', 404);
        }

        return $this->jsonResponse(true, $property, 'بيانات العقار');
    }

    /** معالجة طلبات العقارات */
    private function handlePropertyRequest(Request $request, callable $resultHandler, string $message): JsonResponse
    {
        $query = Property::whereIn('status', ['مفتوح', 'تم البيع'])->with('images');
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $result = $resultHandler($query);

        if ((is_array($result) && empty($result)) || ($result instanceof LengthAwarePaginator && $result->isEmpty())) {
            return $this->jsonResponse(false, [], 'لا توجد عقارات تطابق بحثك حالياً', 200, true);
        }

        $responseData = ['data' => is_array($result) ? $result : $result->items()];

        if ($result instanceof LengthAwarePaginator) {
            $responseData['pagination'] = [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total()
            ];
        }

        return $this->jsonResponse(true, $responseData, $message, 200, true);
    }

    /** تطبيق الفلاتر */
    private function applyFilters($query, Request $request): void
    {
        $filters = [
            'region' => 'like',
            'city' => 'like',
            'land_type' => '=',
            'purpose' => '='
        ];

        foreach ($filters as $field => $operator) {
            if ($request->filled($field)) {
                $value = $operator === 'like' ? '%' . $request->$field . '%' : $request->$field;
                $query->where($field, $operator, $value);
            }
        }

        // فلاتر النطاقات
        $rangeFilters = [
            'min_area' => ['total_area', '>='],
            'max_area' => ['total_area', '<='],
            'min_price' => ['price_per_sqm', '>='],
            'max_price' => ['price_per_sqm', '<='],
            'min_investment' => ['estimated_investment_value', '>='],
            'max_investment' => ['estimated_investment_value', '<='],
            'max_duration' => ['investment_duration', '<=']
        ];

        foreach ($rangeFilters as $param => [$field, $operator]) {
            if ($request->filled($param)) {
                $query->where($field, $operator, $request->$param);
            }
        }

        // البحث
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhere('region', 'like', $searchTerm)
                    ->orWhere('city', 'like', $searchTerm);
            });
        }
    }

    /** تطبيق الترتيب */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = [
            'created_at',
            'total_area',
            'price_per_sqm',
            'estimated_investment_value',
            'investment_duration'
        ];

        $query->orderBy(
            in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at',
            $sortOrder
        );
    }

    /** دالة مساعدة للردود JSON */
    private function jsonResponse(bool $status, $data, string $message, int $code = 200, bool $includeFilters = false): JsonResponse
    {
        $response = [
            'status' => $status,
            'data' => $data,
            'message' => $status ? "تم جلب {$message} بنجاح" : $message
        ];

        if ($includeFilters) {
            $response['filters_applied'] = $this->getAppliedFilters(request());
        }

        return response()->json($response, $code);
    }

    /** الحصول على الفلاتر المطبقة */
    private function getAppliedFilters(Request $request): array
    {
        $filterFields = [
            'region',
            'city',
            'land_type',
            'purpose',
            'min_area',
            'max_area',
            'min_price',
            'max_price',
            'min_investment',
            'max_investment',
            'max_duration',
            'date_from',
            'date_to',
            'search',
            'sort_by',
            'sort_order'
        ];

        return array_filter($request->only($filterFields));
    }
}
