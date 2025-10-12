<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Models\LandListing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\Landlistings\LandListingCreateRequest;
use App\Http\Requests\User\Landlistings\LandListingUpdateRequest;

class LandListingController extends BaseLandController
{
    /**
     * عرض جميع قوائم الأراضي (مع إمكانية التصفية)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // إضافة شرط لجلب الأراضي المقبولة فقط
            $query = LandListing::with('user')->where('status', 'مقبول');

            // التصفية حسب نوع الأرض
            if ($request->has('land_type')) {
                $query->where('land_type', $request->land_type);
            }

            // التصفية حسب الغرض
            if ($request->has('purpose')) {
                $query->where('purpose', $request->purpose);
            }

            // التصفية حسب الموقع (بحث)
            if ($request->has('location')) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            // التصفية حسب المساحة (الحد الأدنى)
            if ($request->has('min_area')) {
                $query->where('area', '>=', $request->min_area);
            }

            // التصفية حسب المساحة (الحد الأقصى)
            if ($request->has('max_area')) {
                $query->where('area', '<=', $request->max_area);
            }

            // الترتيب
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $landListings = $query->paginate($perPage);

            $formattedListings = $landListings->getCollection()->map(function ($listing) {
                return $this->formatLandListingSummary($listing);
            });

            return $this->successResponse([
                'land_listings' => $formattedListings,
                'pagination' => [
                    'current_page' => $landListings->currentPage(),
                    'last_page' => $landListings->lastPage(),
                    'per_page' => $landListings->perPage(),
                    'total' => $landListings->total(),
                ]
            ], 'تم جلب قوائم الأراضي بنجاح');
        } catch (\Exception $e) {
            $this->logError($e, 'Land listings index error');
            return $this->errorResponse('حدث خطأ أثناء جلب قوائم الأراضي', 500);
        }
    }


    /**
     * عرض تفاصيل قائمة أرض محددة
     */
    public function show($id): JsonResponse
    {
        try {
            $landListing = LandListing::with('user')
                ->where('id', $id)
                ->where('status', 'مقبول') // نجيب فقط الأراضي المقبولة
                ->first();

            if (!$landListing) {
                return $this->errorResponse('قائمة الأرض غير موجودة أو لم يتم الموافقة عليها', 404);
            }

            return $this->successResponse(
                $this->formatLandListingData($landListing),
                'تم جلب تفاصيل الأرض بنجاح'
            );
        } catch (\Exception $e) {
            $this->logError($e, 'Land listing show error');
            return $this->errorResponse('حدث خطأ أثناء جلب تفاصيل الأرض', 500);
        }
    }

    /**
     * إنشاء قائمة أرض جديدة
     */
    public function store(LandListingCreateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['user_id'] = $request->user()->id;

            $landListing = LandListing::create($validated);
            $landListing->load('user');

            return $this->successResponse(
                $this->formatLandListingData($landListing),
                'تم إنشاء قائمة الأرض بنجاح',
                201
            );
        } catch (\Exception $e) {
            $this->logError($e, 'Land listing store error');
            return $this->errorResponse('حدث خطأ أثناء إنشاء قائمة الأرض', 500);
        }
    }

    /**
     * تحديث قائمة أرض
     */
    public function update(LandListingUpdateRequest $request, $id): JsonResponse
    {
        try {
            $landListing = LandListing::where('user_id', $request->user()->id)->find($id);

            if (!$landListing) {
                return $this->errorResponse('قائمة الأرض غير موجودة أو لا تملك صلاحية التعديل', 404);
            }

            $validated = $request->validated();
            $landListing->update($validated);
            $landListing->load('user');

            return $this->successResponse(
                $this->formatLandListingData($landListing),
                'تم تحديث قائمة الأرض بنجاح'
            );
        } catch (\Exception $e) {
            $this->logError($e, 'Land listing update error');
            return $this->errorResponse('حدث خطأ أثناء تحديث قائمة الأرض', 500);
        }
    }

    /**
     * حذف قائمة أرض
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $landListing = LandListing::where('user_id', $request->user()->id)->find($id);

            if (!$landListing) {
                return $this->errorResponse('قائمة الأرض غير موجودة أو لا تملك صلاحية الحذف', 404);
            }

            $landListing->delete();

            return $this->successResponse(null, 'تم حذف قائمة الأرض بنجاح');
        } catch (\Exception $e) {
            $this->logError($e, 'Land listing destroy error');
            return $this->errorResponse('حدث خطأ أثناء حذف قائمة الأرض', 500);
        }
    }
}
