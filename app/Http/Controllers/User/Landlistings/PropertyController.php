<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Http\Requests\User\Landlistings\PropertyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    /** جلب العقارات للواجهة العامة (الزوار) */
    public function indexPublic()
    {
        $properties = Property::accepted() // فقط العقارات المقبولة
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse($properties, 'تم جلب العقارات بنجاح');
    }

    /** عرض عقار محدد للواجهة العامة */
    public function showPublic($id)
    {
        $property = Property::accepted()->find($id);

        if (!$property) {
            return $this->errorResponse('العقار غير موجود', 404);
        }

        return $this->successResponse($property, 'تم جلب بيانات العقار بنجاح');
    }

    // رسائل جاهزة
    private const MSG_NOT_FOUND = 'العقار غير موجود أو لا تملك صلاحية الوصول إليه';
    private const MSG_UNAUTHORIZED = 'لا يمكن تنفيذ هذا الإجراء على العقار في حالته الحالية';

    /** عرض جميع العقارات المقبولة */
    public function index(Request $request)
    {
        $user = $request->user();

        $properties = Property::forUser($user->id)
            ->accepted()
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse($properties, 'تم جلب العقارات المقبولة بنجاح');
    }

    /** عرض عقار محدد */
    public function show(Request $request, $id)
    {
        $property = $this->findProperty($request, $id, true);
        if (!$property) return $this->errorResponse(self::MSG_NOT_FOUND, 404);

        return $this->successResponse($property, 'تم جلب بيانات العقار بنجاح');
    }

    /** إنشاء عقار جديد */
    public function store(PropertyRequest $request)
    {
        $user = $request->user();

        try {
            $images = $this->handleImages($request);

            $property = Property::create(array_merge($request->validated(), [
                'user_id' => $user->id,
                'images' => $images,
                'legal_declaration' => $request->legal_declaration ? 'نعم' : 'لا',
                'status' => 'قيد المراجعة',
            ]));

            return $this->successResponse($property, 'تم إنشاء العقار بنجاح وجاري مراجعته', 201);
        } catch (\Exception $e) {
            if (!empty($images)) $this->deleteImages($images);
            return $this->errorResponse('حدث خطأ أثناء إنشاء العقار: ' . $e->getMessage());
        }
    }

    /** تحديث عقار موجود */
    public function update(PropertyRequest $request, $id)
    {
        $property = $this->findProperty($request, $id);
        if (!$property) return $this->errorResponse(self::MSG_NOT_FOUND, 404);

        if (in_array($property->status, ['تم البيع', 'مقبول'])) {
            return $this->errorResponse(self::MSG_UNAUTHORIZED, 403);
        }

        try {
            $images = $this->handleImages($request, $property->images ?? []);

            $property->update(array_merge($request->validated(), [
                'images' => $images,
                'legal_declaration' => $request->legal_declaration ? 'نعم' : 'لا',
                'status' => 'قيد المراجعة',
            ]));

            return $this->successResponse($property, 'تم تحديث العقار بنجاح وجاري مراجعته');
        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ أثناء تحديث العقار: ' . $e->getMessage());
        }
    }

    /** حذف عقار */
    public function destroy(Request $request, $id)
    {
        $property = $this->findProperty($request, $id);
        if (!$property) return $this->errorResponse(self::MSG_NOT_FOUND, 404);

        if ($property->status === 'تم البيع') {
            return $this->errorResponse(self::MSG_UNAUTHORIZED, 403);
        }

        $this->deleteImages($property->images ?? []);
        $property->delete();

        return $this->successResponse(null, 'تم حذف العقار بنجاح');
    }

    /** جلب العقارات حسب الحالة */
    public function getByStatus(Request $request, $status)
    {
        $allowedStatuses = ['قيد المراجعة', 'مقبول', 'مرفوض', 'تم البيع'];
        if (!in_array($status, $allowedStatuses)) {
            return $this->errorResponse('حالة غير صحيحة', 400);
        }

        $user = $request->user();
        $properties = Property::forUser($user->id)
            ->withStatus($status)
            ->orderByDesc('created_at')
            ->get();

        return $this->successResponse($properties, "تم جلب العقارات ذات الحالة: {$status}");
    }

    /** إحصائيات العقارات */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $stats = [
            'total' => Property::forUser($user->id)->count(),
            'under_review' => Property::forUser($user->id)->withStatus('قيد المراجعة')->count(),
            'approved' => Property::forUser($user->id)->withStatus('مقبول')->count(),
            'rejected' => Property::forUser($user->id)->withStatus('مرفوض')->count(),
            'sold' => Property::forUser($user->id)->withStatus('تم البيع')->count(),
        ];

        return $this->successResponse($stats, 'تم جلب الإحصائيات بنجاح');
    }

    // -------------------- دوال مساعدة --------------------

    /** جلب العقار مع التحقق من الملكية والحالة */
    private function findProperty(Request $request, $id, $mustBeAccepted = false)
    {
        $query = Property::forUser($request->user()->id)->where('id', $id);
        if ($mustBeAccepted) $query->accepted();
        return $query->first();
    }

    /** معالجة الصور */
    private function handleImages(Request $request, array $oldImages = [])
    {
        $images = $oldImages;

        if ($request->hasFile('images')) {
            // حذف الصور القديمة إذا كانت موجودة
            if (!empty($oldImages)) $this->deleteImages($oldImages);

            $images = [];
            foreach ($request->file('images') as $image) {
                $imageName = 'property_' . Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
                $images[] = $image->storeAs('properties/images', $imageName, 'public');
            }
        }

        return $images;
    }

    /** حذف الصور */
    private function deleteImages(array $images)
    {
        foreach ($images as $image) {
            Storage::disk('public')->delete($image);
        }
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
