<?php

namespace App\Http\Controllers\Admin\Landlistings;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminPropertyStatusController extends Controller
{
    /**
     * الموافقة على الأرض (تغيير الحالة إلى مفتوح)
     */
    public function approveProperty($id): JsonResponse
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'الارض غير موجودة'
                ], 404);
            }

            $property->update([
                'status' => 'مفتوح'
            ]);

            return response()->json([
                'success' => true,
                'data' => $property,
                'message' => 'تم الموافقة على الأرض بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الموافقة على الأرض: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * رفض الأرض (تغيير الحالة إلى مرفوض)
     */
    public function rejectProperty($id): JsonResponse
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'الارض غير موجودة'
                ], 404);
            }

            $property->update([
                'status' => 'مرفوض'
            ]);

            return response()->json([
                'success' => true,
                'data' => $property,
                'message' => 'تم رفض الأرض بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفض الأرض: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تعيين الأرض كمباعة (تغيير الحالة إلى تم البيع)
     */
    public function markAsSold($id): JsonResponse
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'الارض غير موجودة'
                ], 404);
            }

            $property->update([
                'status' => 'تم البيع'
            ]);

            return response()->json([
                'success' => true,
                'data' => $property,
                'message' => 'تم تعيين الأرض كمباعة بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعيين الأرض كمباعة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إعادة الأرض إلى قيد المراجعة
     */
    public function returnToPending($id): JsonResponse
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'الارض غير موجودة'
                ], 404);
            }

            $property->update([
                'status' => 'قيد المراجعة'
            ]);

            return response()->json([
                'success' => true,
                'data' => $property,
                'message' => 'تم إعادة الأرض إلى قيد المراجعة بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة الأرض إلى قيد المراجعة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تغيير حالة الأرض بشكل عام (للاستخدام المتقدم)
     */
    public function changePropertyStatus(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:قيد المراجعة,مفتوح,مرفوض,تم البيع'
            ]);

            $property = Property::find($id);

            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'الارض غير موجودة'
                ], 404);
            }

            $property->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'data' => $property,
                'message' => 'تم تغيير حالة الأرض بنجاح إلى: ' . $request->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير حالة الأرض: ' . $e->getMessage()
            ], 500);
        }
    }
}