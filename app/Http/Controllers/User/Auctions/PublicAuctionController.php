<?php

namespace App\Http\Controllers\User\Auctions;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicAuctionController extends Controller
{
    /**
     * جلب آخر المزادات (لواجهة الموقع الرئيسية)
     */
    public function latest(Request $request): JsonResponse
    {
        return $this->handleAuctionQuery($request, function ($query) use ($request) {
            $limit = $request->get('limit', 7);
            return $query->take($limit)->get();
        }, 'آخر المزادات');
    }

    /**
     * عرض قائمة المزادات العامة
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleAuctionQuery($request, function ($query) use ($request) {
            return $query->paginate($request->get('per_page', 12));
        }, 'المزادات');
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

            return $this->jsonResponse($auction, 'تفاصيل المزاد');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('المزاد غير موجود أو غير مفتوح', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('تفاصيل المزاد', $e->getMessage());
        }
    }

    /**
     * البحث المتقدم في المزادات
     */
    public function search(Request $request): JsonResponse
    {
        return $this->handleAuctionQuery($request, function ($query) use ($request) {
            $sortBy = $request->get('sort_by', 'auction_date');
            $sortOrder = $request->get('sort_order', 'desc');

            return $query->orderBy($sortBy, $sortOrder)
                ->paginate($request->get('per_page', 12));
        }, 'البحث في المزادات');
    }

    /**
     * دالة مساعدة لمعالجة استعلامات المزادات
     */
    private function handleAuctionQuery(Request $request, callable $resultHandler, string $message): JsonResponse
    {
        try {
            $query = $this->buildBaseQuery($request);
            $this->applyFilters($query, $request);

            $result = $resultHandler($query);

            // التحقق إذا كانت النتيجة فارغة
            if ($result->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => 'لا يوجد مزادات تطابق بحثك حالياً'
                ]);
            }

            return $this->jsonResponse($result, $message);
        } catch (\Exception $e) {
            return $this->errorResponse($message, $e->getMessage());
        }
    }

    /**
     * بناء الاستعلام الأساسي
     */
    private function buildBaseQuery(Request $request)
    {
        $statuses = $request->route()->getName() === 'auctions.latest'
            ? ['مفتوح', 'تم البيع']
            : ['مفتوح'];

        return Auction::whereIn('status', $statuses)
            ->with(['company:id,user_id,auction_name', 'images', 'videos'])
            ->orderBy('auction_date', 'desc');
    }

    /**
     * تطبيق الفلاتر على الاستعلام
     */
    private function applyFilters($query, Request $request): void
    {
        // البحث بالكلمة المفتاحية
        $searchField = $request->filled('search') ? 'search' : 'keyword';
        if ($request->filled($searchField)) {
            $keyword = $request->$searchField;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%");
            });
        }

        // الفلترة بالتاريخ
        if ($request->filled('date')) {
            $query->whereDate('auction_date', $request->date);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('auction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('auction_date', '<=', $request->end_date);
        }

        // الفلترة بالمنطقة
        if ($request->filled('region')) {
            $query->where('address', 'LIKE', "%{$request->region}%");
        }
    }

    /**
     * دالة مساعدة للرد الناجح
     */
    private function jsonResponse($data, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => "تم جلب {$message} بنجاح"
        ]);
    }

    /**
     * دالة مساعدة للرد بالخطأ
     */
    private function errorResponse(string $message, string $error = '', int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "حدث خطأ أثناء {$message}",
            'error' => $error
        ], $code);
    }
}
