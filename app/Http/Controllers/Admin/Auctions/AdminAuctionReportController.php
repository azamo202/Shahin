<?php

namespace App\Http\Controllers\Admin\Auctions;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuctionReportController extends Controller
{
    private const DEFAULT_SORT_FIELD = 'auction_date';
    private const DEFAULT_SORT_DIRECTION = 'desc';
    private const ALL_STATUS = 'الكل';

    /**
     * تقرير المزادات الكامل مع الفلترة والترتيب
     * GET /api/admin/auctions/report
     */
    public function report(Request $request): JsonResponse
    {
        try {
            $query = Auction::with([
                'company:id,user_id,auction_name',
                'company.user:id,full_name,email,phone'
            ]);

            $this->applySearchFilter($query, $request);
            $this->applyStatusFilter($query, $request);
            $this->applyDateFilter($query, $request);
            $this->applyCompanyFilter($query, $request);
            $this->applySorting($query, $request);

            $auctions = $query->get()->map(fn($auction) => $this->formatAuctionData($auction));

            return response()->json([
                'success' => true,
                'data' => $auctions,
                'count' => $auctions->count(),
                'message' => 'تم جلب تقرير المزادات بنجاح'
            ]);

        } catch (\Exception $e) {
            return $this->handleError($e, 'حدث خطأ أثناء إنشاء تقرير المزادات');
        }
    }

    /**
     * تطبيق فلترة البحث
     */
    private function applySearchFilter($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }
    }

    /**
     * تطبيق فلترة الحالة
     */
    private function applyStatusFilter($query, Request $request): void
    {
        if ($request->filled('status') && $request->status !== self::ALL_STATUS) {
            $query->where('status', $request->status);
        }
    }

    /**
     * تطبيق فلترة التاريخ
     */
    private function applyDateFilter($query, Request $request): void
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('auction_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('auction_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('auction_date', '<=', $request->to_date);
        }
    }

    /**
     * تطبيق فلترة الشركة
     */
    private function applyCompanyFilter($query, Request $request): void
    {
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
    }

    /**
     * تطبيق الترتيب
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort_field', self::DEFAULT_SORT_FIELD);
        $sortDirection = $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION);

        $allowedSortFields = ['id', 'title', 'status', 'auction_date', 'created_at', 'address'];
        $allowedDirections = ['asc', 'desc'];

        $finalSortField = in_array($sortField, $allowedSortFields) ? $sortField : self::DEFAULT_SORT_FIELD;
        $finalSortDirection = in_array($sortDirection, $allowedDirections) ? $sortDirection : self::DEFAULT_SORT_DIRECTION;

        $query->orderBy($finalSortField, $finalSortDirection);
    }

    /**
     * تنسيق بيانات المزاد
     */
    private function formatAuctionData(Auction $auction): array
    {
        return [
            'id' => $auction->id,
            'title' => $auction->title,
            'status' => $auction->status,
            'auction_date' => $auction->auction_date,
            'address' => $auction->address,
            'company_name' => $auction->company->auction_name ?? 'غير محدد',
            'owner_name' => $auction->company->user->full_name ?? 'غير محدد',
            'owner_email' => $auction->company->user->email ?? 'غير متوفر',
            'created_at' => $auction->created_at->format('Y-m-d H:i'),
        ];
    }

    /**
     * معالجة الأخطاء
     */
    private function handleError(\Exception $e, string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $e->getMessage()
        ], 500);
    }
}