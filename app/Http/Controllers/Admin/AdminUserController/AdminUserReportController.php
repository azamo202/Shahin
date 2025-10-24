<?php

namespace App\Http\Controllers\Admin\AdminUserController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminUserReportController extends Controller
{
    private const DEFAULT_SORT_FIELD = 'created_at';
    private const DEFAULT_SORT_DIRECTION = 'desc';
    private const CACHE_TTL = 300; // 5 دقائق

    /**
     * تقرير المستخدمين للإدارة
     * GET /api/admin/users/report
     */
    public function report(Request $request): JsonResponse
    {
        $cacheKey = $this->generateCacheKey($request);
        
        $reportData = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request) {
            return $this->generateReportData($request);
        });

        return response()->json([
            'success' => true,
            'data' => $reportData['users'],
            'count' => $reportData['count'],
            'meta' => $reportData['meta']
        ]);
    }

    /**
     * توليد بيانات التقرير
     */
    private function generateReportData(Request $request): array
    {
        $query = User::with([
            'userType',
            'landOwner',
            'legalAgent', 
            'businessEntity',
            'realEstateBroker',
            'auctionCompany'
        ]);

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $users = $query->get()->map(fn($user) => $this->formatUserData($user));

        return [
            'users' => $users,
            'count' => $users->count(),
            'meta' => $this->getMetaData($request)
        ];
    }

    /**
     * تطبيق الفلاتر على الاستعلام
     */
    private function applyFilters($query, Request $request): void
    {
        // فلترة حسب البحث في الاسم
        if ($request->filled('search')) {
            $query->where('full_name', 'LIKE', '%' . $request->search . '%');
        }

        // فلترة حسب الحالة
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // فلترة حسب نوع المستخدم
        if ($request->filled('user_type_id')) {
            $query->where('user_type_id', $request->user_type_id);
        }
    }

    /**
     * تطبيق الترتيب على الاستعلام
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort_field', self::DEFAULT_SORT_FIELD);
        $sortDirection = $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION);

        $allowedSortFields = ['id', 'full_name', 'email', 'created_at', 'status'];
        $allowedDirections = ['asc', 'desc'];

        $finalSortField = in_array($sortField, $allowedSortFields) ? $sortField : self::DEFAULT_SORT_FIELD;
        $finalSortDirection = in_array($sortDirection, $allowedDirections) ? $sortDirection : self::DEFAULT_SORT_DIRECTION;

        $query->orderBy($finalSortField, $finalSortDirection);
    }

    /**
     * تنسيق بيانات المستخدم للتقرير
     */
    private function formatUserData(User $user): array
    {
        $baseData = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'user_type' => $user->userType->type_name ?? 'غير محدد',
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'details' => $this->getUserDetails($user)
        ];

        return $baseData;
    }

    /**
     * الحصول على تفاصيل المستخدم حسب نوعه
     */
    private function getUserDetails(User $user): ?object
    {
        $detailRelations = [
            'landOwner',
            'legalAgent', 
            'businessEntity',
            'realEstateBroker',
            'auctionCompany'
        ];

        foreach ($detailRelations as $relation) {
            if ($user->$relation) {
                return $user->$relation;
            }
        }

        return null;
    }

    /**
     * توليد مفتاح التخزين المؤقت
     */
    private function generateCacheKey(Request $request): string
    {
        return 'user_report_' . md5(serialize([
            'search' => $request->search,
            'status' => $request->status,
            'user_type_id' => $request->user_type_id,
            'sort_field' => $request->sort_field,
            'sort_direction' => $request->sort_direction
        ]));
    }

    /**
     * الحصول على بيانات وصفية إضافية
     */
    private function getMetaData(Request $request): array
    {
        return [
            'filters_applied' => [
                'search' => $request->search,
                'status' => $request->status,
                'user_type_id' => $request->user_type_id
            ],
            'sorting' => [
                'field' => $request->get('sort_field', self::DEFAULT_SORT_FIELD),
                'direction' => $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION)
            ],
            'generated_at' => now()->toIso8601String()
        ];
    }
}