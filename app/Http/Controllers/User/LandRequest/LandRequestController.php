<?php

namespace App\Http\Controllers\User\LandRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LandRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\LandRequest\StoreLandRequest;
use App\Http\Requests\User\LandRequest\UpdateLandRequest;

class LandRequestController extends Controller
{
    /**
     * إنشاء طلب جديد
     */
    public function store(StoreLandRequest $request)
    {
        $landRequest = LandRequest::create([
            'user_id' => Auth::id(),
            'region' => $request->region,
            'city' => $request->city,
            'purpose' => $request->purpose,
            'type' => $request->type,
            'area' => $request->area,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => '✅ تم إنشاء طلب الأرض بنجاح',
            'data' => $landRequest,
        ], 201);
    }

    /**
     * تحديث طلب موجود
     */
    public function update(UpdateLandRequest $request, $id)
    {
        $landRequest = LandRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $landRequest->update([
            'region' => $request->region,
            'city' => $request->city,
            'purpose' => $request->purpose,
            'type' => $request->type,
            'area' => $request->area,
            'description' => $request->description,
            'status' => 'pending', // إعادة الطلب إلى قيد المراجعة بعد التحديث
        ]);

        return response()->json([
            'message' => '✅ تم تحديث طلب الأرض بنجاح وهو الآن قيد المراجعة',
            'data' => $landRequest,
        ]);
    }


    /**
     * جلب الطلبات الخاصة بالمستخدم نفسه
     */
    public function myRequests()
    {
        $requests = LandRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'message' => '📋 قائمة طلباتك',
            'data' => $requests,
        ]);
    }

    /**
     * عرض جميع الطلبات لجميع المستخدمين مع نظام فلترة متقدم
     * سيتم جلب الطلبات التي حالتها فقط "open" أو "completed"
     */
    public function allRequests(Request $request)
    {
        $query = LandRequest::select(
            'id',
            'region',
            'city',
            'purpose',
            'type',
            'area',
            'description',
            'status',
            'created_at'
        )
            ->whereIn('status', ['open', 'completed']); // فقط الطلبات المفتوحة أو المكتملة

        $filters = $request->only(['region', 'city', 'purpose', 'type', 'area']);

        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                switch ($field) {
                    case 'region':
                    case 'city':
                        $query->where($field, 'like', '%' . $value . '%');
                        break;
                    case 'purpose':
                    case 'type':
                        $query->where($field, $value);
                        break;
                    case 'area':
                        $query->where('area', '>=', $value);
                        break;
                }
            }
        }

        if ($request->filled('area_min')) {
            $query->where('area', '>=', $request->area_min);
        }

        if ($request->filled('area_max')) {
            $query->where('area', '<=', $request->area_max);
        }

        $requests = $query->orderByDesc('created_at')->get();

        return response()->json([
            'message' => '🌍 جميع الطلبات المتاحة',
            'data' => $requests,
            'filters' => [
                'region' => $request->region,
                'city' => $request->city,
                'purpose' => $request->purpose,
                'type' => $request->type,
                'area' => $request->area,
                'area_min' => $request->area_min,
                'area_max' => $request->area_max,
            ],
        ]);
    }

    /**
 * عرض تفاصيل طلب محدد
 * سيتم عرض الطلب فقط إذا كانت حالته "open" أو "completed"
 */
/**
 * عرض تفاصيل طلب محدد
 * سيتم عرض الطلب فقط إذا كانت حالته "open" أو "completed"
 */
public function show($id)
{
    $requestData = LandRequest::select(
        'id',
        'region',
        'city',
        'purpose',
        'type',
        'area',
        'description',
        'status',
        'created_at'
    )
    ->whereIn('status', ['open', 'completed'])
    ->where('id', $id)
    ->first(); // استخدمنا first() بدل findOrFail للتحكم بالرد

    if (!$requestData) {
        return response()->json([
            'message' => '❌ لم يتم العثور على الطلب أو أن حالته لا تسمح بالعرض.',
            'data' => null,
        ], 404);
    }

    return response()->json([
        'message' => '📄 تفاصيل الطلب',
        'data' => $requestData,
    ]);
}
}
