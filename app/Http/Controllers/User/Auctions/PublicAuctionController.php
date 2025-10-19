<?php

namespace App\Http\Controllers\User\Auctions;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicAuctionController extends Controller
{
    /**
     * جلب آخر المزادات (لواجهة الموقع الرئيسية)
     */
    public function latest(Request $request): JsonResponse
    {
        try {
            // عدد النتائج (افتراضي 7 ويمكن تغييره من الفرونت)
            $limit = $request->get('limit', 7);

            $query = Auction::whereIn('status', ['مفتوح', 'تم البيع'])
                ->with(['company:id,user_id,auction_name', 'images', 'videos']);

            // تطبيق عوامل الفلترة
            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            }

            if ($request->filled('start_date')) {
                $query->whereDate('auction_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('auction_date', '<=', $request->end_date);
            }

            if ($request->filled('region')) {
                $query->where('address', 'LIKE', "%{$request->region}%");
            }

            // ترتيب النتائج بحسب التاريخ أو آخر إضافة
            $auctions = $query->orderBy('auction_date', 'desc')
                ->take($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $auctions,
                'message' => 'تم جلب آخر المزادات بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب آخر المزادات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض قائمة المزادات العامة
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Auction::where('status', 'مفتوح')
                ->with([
                    'company:id,user_id,auction_name', // الشركة المرتبطة
                    'images',
                    'videos'
                ])
                ->orderBy('auction_date', 'desc');

            // البحث بالكلمة المفتاحية
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('address', 'LIKE', "%{$searchTerm}%");
                });
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
            $auctions = $query->paginate($request->get('per_page', 12));

            return response()->json([
                'success' => true,
                'data' => $auctions,
                'message' => 'تم جلب المزادات بنجاح'
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
     * عرض تفاصيل مزاد معين
     */
    public function show($id): JsonResponse
    {
        try {
            $auction = Auction::where('status', 'مفتوح')
                ->with(['company:id,user_id,auction_name', 'images', 'videos'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $auction,
                'message' => 'تم جلب تفاصيل المزاد بنجاح'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المزاد غير موجود أو غير مفتوح'
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
     * البحث المتقدم في المزادات
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = Auction::where('status', 'مفتوح')
                ->with(['company:id,user_id,auction_name', 'images', 'videos']);

            // تطبيق جميع عوامل التصفية
            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('description', 'LIKE', "%{$keyword}%");
                });
            }

            if ($request->filled('start_date')) {
                $query->whereDate('auction_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('auction_date', '<=', $request->end_date);
            }

            if ($request->filled('region')) {
                $query->where('address', 'LIKE', "%{$request->region}%");
            }

            // ترتيب النتائج
            $sortBy = $request->get('sort_by', 'auction_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $auctions = $query->paginate($request->get('per_page', 12));

            return response()->json([
                'success' => true,
                'data' => $auctions,
                'message' => 'تم البحث في المزادات بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء البحث في المزادات',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
