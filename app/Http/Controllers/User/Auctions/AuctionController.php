<?php

namespace App\Http\Controllers\User\Auctions;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auctions\StoreAuctionRequest;
use App\Http\Requests\User\Auctions\UpdateAuctionRequest;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    // الرسائل الجاهزة
    private const MSG_NOT_FOUND = 'المزاد غير موجود أو لا تملك صلاحية الوصول إليه';
    private const MSG_UNAUTHORIZED = 'لا يمكن تنفيذ هذا الإجراء على المزاد في حالته الحالية';
    private const MSG_FORBIDDEN = 'غير مسموح لك بتنفيذ هذا الإجراء';

    /** تحقق من نوع المستخدم */
    private function authorizeCompany(Request $request)
    {
        if ($request->user()->user_type_id !== 6) {
            abort(response()->json([
                'status' => false,
                'message' => self::MSG_FORBIDDEN
            ], 403));
        }
    }

    /** عرض كل مزادات المستخدم */
    public function index(Request $request)
    {
        $this->authorizeCompany($request);
        $user = $request->user();
        $auctions = $user->auctions()->latest()->get();
        return $this->successResponse($auctions, 'تم جلب المزادات الخاصة بك بنجاح');
    }

    /** عرض مزاد محدد */
    public function show(Request $request, $id)
    {
        $this->authorizeCompany($request);
        $auction = $this->findAuction($request, $id);
        if (!$auction) return $this->errorResponse(self::MSG_NOT_FOUND, 404);
        return $this->successResponse($auction, 'تم جلب بيانات المزاد بنجاح');
    }

    /** إنشاء مزاد جديد */
    public function store(StoreAuctionRequest $request)
    {
        $this->authorizeCompany($request);
        $user = $request->user();

        DB::beginTransaction();
        try {
            $data = $request->validated();

            // التعامل مع صورة الغلاف إن وجدت
            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('auctions', 'public');
                $data['cover_image'] = $path;
            }

            $auction = $user->auctions()->create(array_merge(
                $data,
                ['status' => 'قيد المراجعة']
            ));


            // حفظ الصور
            if ($request->has('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('auctions/images', 'public');
                    $auction->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            // حفظ الفيديوهات
            if ($request->has('videos')) {
                foreach ($request->file('videos') as $video) {
                    $path = $video->store('auctions/videos', 'public');
                    $auction->videos()->create([
                        'video_path' => $path
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse($auction, 'تم إنشاء المزاد بنجاح وجاري مراجعته', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('حدث خطأ أثناء إنشاء المزاد: ' . $e->getMessage());
        }
    }

    /** تحديث المزاد */
    public function update(UpdateAuctionRequest $request, $id)
    {
        $this->authorizeCompany($request);
        $auction = $this->findAuction($request, $id);
        if (!$auction) return $this->errorResponse(self::MSG_NOT_FOUND, 404);

        if (in_array($auction->status, ['تم البيع', 'قيدالمراجعة', 'مفتوح'])) {
            return $this->errorResponse(self::MSG_UNAUTHORIZED, 403);
        }

        DB::beginTransaction();
        try {
            $data = $request->validated();

            // التعامل مع تحديث صورة الغلاف إن وجدت
            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('auctions', 'public');
                $data['cover_image'] = $path;
            }

            $auction->update(array_merge($data, ['status' => 'قيد المراجعة']));
            DB::commit();
            return $this->successResponse($auction, 'تم تحديث المزاد بنجاح وجاري مراجعته');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('حدث خطأ أثناء تحديث المزاد: ' . $e->getMessage());
        }
    }

    /** حذف المزاد */
    public function destroy(Request $request, $id)
    {
        $this->authorizeCompany($request);
        $auction = $this->findAuction($request, $id);
        if (!$auction) return $this->errorResponse(self::MSG_NOT_FOUND, 404);

        if ($auction->status === 'مفتوح') {
            return $this->errorResponse(self::MSG_UNAUTHORIZED, 403);
        }

        try {
            $auction->delete();
            return $this->successResponse(null, 'تم حذف المزاد بنجاح');
        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء حذف المزاد: ' . $e->getMessage());
        }
    }

    /** جلب المزادات حسب الحالة */
    public function getByStatus(Request $request, $status)
    {
        $this->authorizeCompany($request);
        $allowedStatuses = ['قيد المراجعة', 'مفتوح', 'مرفوض'];
        if (!in_array($status, $allowedStatuses)) {
            return $this->errorResponse('حالة غير صحيحة', 400);
        }

        $user = $request->user();
        $auctions = $user->auctions()->where('status', $status)->latest()->get();
        return $this->successResponse($auctions, "تم جلب المزادات ذات الحالة: {$status}");
    }

    /** إحصائيات المزادات */
    public function getStats(Request $request)
    {
        $this->authorizeCompany($request);
        $user = $request->user();
        $stats = [
            'total' => $user->auctions()->count(),
            'under_review' => $user->auctions()->where('status', 'قيد المراجعة')->count(),
            'approved' => $user->auctions()->where('status', 'مفتوح')->count(),
            'rejected' => $user->auctions()->where('status', 'مرفوض')->count(),
        ];
        return $this->successResponse($stats, 'تم جلب إحصائيات المزادات بنجاح');
    }

    // -------------------- دوال مساعدة --------------------

    /** جلب المزاد والتحقق من الملكية */
    private function findAuction(Request $request, $id)
    {
        return $request->user()->auctions()->find($id);
    }

    /** رد JSON للنجاح */
    private function successResponse($data, $message = '', $code = 200)
    {
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    /** رد JSON للخطأ */
    private function errorResponse($message, $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], $code);
    }
}
