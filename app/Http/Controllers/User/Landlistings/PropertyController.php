<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Http\Requests\PropertyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    /**     * عرض جميع العقارات المقبولة للمستخدم
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $properties = Property::where('user_id', $user->id)
                ->where('status', 'مقبول') // ✅ عرض العقارات المقبولة فقط
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $properties,
                'message' => 'تم جلب العقارات المقبولة بنجاح'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب العقارات: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * عرض عقار محدد للمستخدم
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $property = Property::where('user_id', $user->id)
                ->where('status', 'مقبول') // ✅ العقار يجب أن يكون مقبول
                ->find($id);

            if (!$property) {
                return response()->json([
                    'status' => false,
                    'message' => 'العقار غير موجود أو لا تملك صلاحية الوصول إليه'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $property,
                'message' => 'تم جلب بيانات العقار بنجاح'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات العقار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء عقار جديد
     */
    public function store(PropertyRequest $request)
    {
        try {
            $user = $request->user();

            // معالجة الصور وحفظها
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = 'property_' . Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('properties/images', $imageName, 'public');
                    $images[] = $imagePath;
                }
            }

            // إنشاء العقار
            $property = Property::create([
                'user_id' => $user->id,
                'announcement_number' => $request->announcement_number,
                'region' => $request->region,
                'city' => $request->city,
                'title' => $request->title,
                'land_type' => $request->land_type,
                'purpose' => $request->purpose,
                'geo_location_text' => $request->geo_location_text,
                'geo_location_map' => $request->geo_location_map,
                'total_area' => $request->total_area,
                'length_north' => $request->length_north,
                'length_south' => $request->length_south,
                'length_east' => $request->length_east,
                'length_west' => $request->length_west,
                'description' => $request->description,
                'deed_number' => $request->deed_number,
                'images' => $images,
                'price_per_sqm' => $request->price_per_sqm,
                'investment_duration' => $request->investment_duration,
                'estimated_investment_value' => $request->estimated_investment_value,
                'agency_number' => $request->agency_number,
                'legal_declaration' => $request->legal_declaration ? 'نعم' : 'لا',
                'status' => 'قيد المراجعة',
            ]);

            return response()->json([
                'status' => true,
                'data' => $property,
                'message' => 'تم إنشاء العقار بنجاح وجاري مراجعته'
            ], 201);
        } catch (\Exception $e) {
            // في حالة الخطأ، حذف الصور التي تم رفعها
            if (!empty($images)) {
                foreach ($images as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إنشاء العقار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديث عقار موجود
     */
    public function update(PropertyRequest $request, $id)
    {
        try {
            $user = $request->user();
            $property = Property::where('user_id', $user->id)->find($id);

            if (!$property) {
                return response()->json([
                    'status' => false,
                    'message' => 'العقار غير موجود أو لا تملك صلاحية التعديل عليه'
                ], 404);
            }

            // التحقق من حالة العقار - لا يمكن تعديل عقار تم بيعه أو مقبول
            if (in_array($property->status, ['تم البيع', 'مقبول'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يمكن تعديل العقار في حالته الحالية'
                ], 403);
            }


            $oldImages = $property->images ?? [];
            $newImages = [];

            // معالجة الصور الجديدة
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = 'property_' . Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('properties/images', $imageName, 'public');
                    $newImages[] = $imagePath;
                }

                // حذف الصور القديمة
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            } else {
                $newImages = $oldImages;
            }

            // تحديث العقار
            $property->update([
                'announcement_number' => $request->announcement_number,
                'region' => $request->region,
                'city' => $request->city,
                'title' => $request->title,
                'land_type' => $request->land_type,
                'purpose' => $request->purpose,
                'geo_location_text' => $request->geo_location_text,
                'geo_location_map' => $request->geo_location_map,
                'total_area' => $request->total_area,
                'length_north' => $request->length_north,
                'length_south' => $request->length_south,
                'length_east' => $request->length_east,
                'length_west' => $request->length_west,
                'description' => $request->description,
                'deed_number' => $request->deed_number,
                'images' => $newImages,
                'price_per_sqm' => $request->price_per_sqm,
                'investment_duration' => $request->investment_duration,
                'estimated_investment_value' => $request->estimated_investment_value,
                'agency_number' => $request->agency_number,
                'legal_declaration' => $request->legal_declaration ? 'نعم' : 'لا',
                'status' => 'قيد المراجعة', // إعادة للحالة قيد المراجعة بعد التعديل
            ]);

            return response()->json([
                'status' => true,
                'data' => $property,
                'message' => 'تم تحديث العقار بنجاح وجاري مراجعته مرة أخرى'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء تحديث العقار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف عقار
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $property = Property::where('user_id', $user->id)->find($id);

            if (!$property) {
                return response()->json([
                    'status' => false,
                    'message' => 'العقار غير موجود أو لا تملك صلاحية الحذف'
                ], 404);
            }

            // التحقق من حالة العقار - لا يمكن حذف عقار تم بيعه
            if ($property->status === 'تم البيع') {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يمكن حذف عقار تم بيعه'
                ], 403);
            }

            // حذف الصور المرتبطة
            if (!empty($property->images)) {
                foreach ($property->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $property->delete();

            return response()->json([
                'status' => true,
                'message' => 'تم حذف العقار بنجاح'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء حذف العقار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض عقارات المستخدم حسب الحالة
     */
    public function getByStatus(Request $request, $status)
    {
        try {
            $user = $request->user();

            // التحقق من أن الحالة مدعومة
            $allowedStatuses = ['قيد المراجعة', 'مقبول', 'مرفوض', 'تم البيع'];
            if (!in_array($status, $allowedStatuses)) {
                return response()->json([
                    'status' => false,
                    'message' => 'حالة غير صحيحة'
                ], 400);
            }

            $properties = Property::where('user_id', $user->id)
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $properties,
                'message' => "تم جلب العقارات ذات الحالة: {$status}"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب العقارات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إحصائيات عقارات المستخدم
     */
    public function getStats(Request $request)
    {
        try {
            $user = $request->user();

            $stats = [
                'total' => Property::where('user_id', $user->id)->count(),
                'under_review' => Property::where('user_id', $user->id)->where('status', 'قيد المراجعة')->count(),
                'approved' => Property::where('user_id', $user->id)->where('status', 'مقبول')->count(),
                'rejected' => Property::where('user_id', $user->id)->where('status', 'مرفوض')->count(),
                'sold' => Property::where('user_id', $user->id)->where('status', 'تم البيع')->count(),
            ];

            return response()->json([
                'status' => true,
                'data' => $stats,
                'message' => 'تم جلب الإحصائيات بنجاح'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات: ' . $e->getMessage()
            ], 500);
        }
    }
}
