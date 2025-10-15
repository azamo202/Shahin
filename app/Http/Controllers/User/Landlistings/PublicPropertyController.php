<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class PublicPropertyController extends Controller
{
    /** جلب العقارات للواجهة العامة (الزوار) */
    public function index()
    {
        $properties = Property::accepted() // فقط العقارات المقبولة
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $properties,
            'message' => 'تم جلب العقارات بنجاح'
        ]);
    }

    /** عرض عقار محدد للواجهة العامة */
    public function show($id)
    {
        $property = Property::accepted()->with('images')->find($id);

        if (!$property) {
            return response()->json([
                'status' => false,
                'message' => 'العقار غير موجود'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $property,
            'message' => 'تم جلب بيانات العقار بنجاح'
        ]);
    }
}
