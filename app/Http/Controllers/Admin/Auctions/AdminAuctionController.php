<?php

namespace App\Http\Controllers\Admin\Auctions;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminAuctionController extends Controller
{
    /**
     * عرض جميع المزادات مع معلومات صاحب المزاد
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Auction::with(['company:id,user_id,auction_name', 'company.user:id,full_name ,email,phone', 'images', 'videos']);

            // البحث بالكلمة المفتاحية
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('address', 'LIKE', "%{$searchTerm}%");
                });
            }

            // الفلترة بحسب الحالة
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // الفلترة بحسب التاريخ
            if ($request->filled('date')) {
                $query->whereDate('auction_date', $request->date);
            }

            // الفلترة بحسب المنطقة
            if ($request->filled('region')) {
                $query->where('address', 'LIKE', "%{$request->region}%");
            }

            // التقسيم إلى صفحات
            $auctions = $query->orderBy('auction_date', 'desc')
                              ->paginate($request->get('per_page', 12));

            return response()->json([
                'success' => true,
                'data' => $auctions,
                'message' => 'تم جلب جميع المزادات بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المزادات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض تفاصيل مزاد محدد
     */
    public function show($id): JsonResponse
    {
        try {
            $auction = Auction::with(['company:id,user_id,auction_name', 'company.user:id,full_name,email,phone', 'images', 'videos'])
                              ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $auction,
                'message' => 'تم جلب تفاصيل المزاد بنجاح'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المزاد غير موجود'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل المزاد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض المزادات قيد المراجعة
     */
    public function pending(): JsonResponse
    {
        try {
            $auctions = Auction::where('status', 'قيد المراجعة')
                               ->with(['company:id,user_id,auction_name', 'company.user:id,full_name,email,phone'])
                               ->orderBy('auction_date', 'desc')
                               ->get();

            return response()->json([
                'success' => true,
                'data' => $auctions,
                'message' => 'تم جلب المزادات قيد المراجعة بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب المزادات قيد المراجعة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * قبول المزاد
     */
    public function approve($id): JsonResponse
    {
        try {
            $auction = Auction::findOrFail($id);
            $auction->status = 'مفتوح';
            $auction->rejection_reason = null; // إزالة أي سبب رفض سابق
            $auction->save();

            return response()->json([
                'success' => true,
                'message' => 'تم قبول المزاد بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء قبول المزاد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * رفض المزاد مع سبب
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $auction = Auction::findOrFail($id);
            $auction->status = 'مرفوض';
            $auction->rejection_reason = $request->reason;
            $auction->save();

            return response()->json([
                'success' => true,
                'message' => 'تم رفض المزاد بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفض المزاد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف المزاد نهائياً
     */
    public function destroy($id): JsonResponse
    {
        try {
            $auction = Auction::findOrFail($id);
            $auction->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المزاد نهائياً'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المزاد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * تغيير حالة المزاد
     */
    public function changeStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:مفتوح,تم البيع,قيد المراجعة,مرفوض'
        ]);

        try {
            $auction = Auction::findOrFail($id);
            $auction->status = $request->status;

            // إذا تم تغيير الحالة إلى مرفوض بدون سبب سابق
            if ($request->status === 'مرفوض' && $request->filled('reason')) {
                $auction->rejection_reason = $request->reason;
            }

            // إزالة سبب الرفض إذا لم يعد مرفوض
            if ($request->status !== 'مرفوض') {
                $auction->rejection_reason = null;
            }

            $auction->save();

            return response()->json([
                'success' => true,
                'message' => 'تم تغيير حالة المزاد بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير حالة المزاد',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إحصائيات المزادات حسب الحالة
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = Auction::selectRaw('status, COUNT(*) as count')
                            ->groupBy('status')
                            ->pluck('count','status');

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'تم جلب إحصائيات المزادات بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
