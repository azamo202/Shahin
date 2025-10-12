<?php

namespace App\Http\Controllers\User\Landlistings;

use App\Models\LandListing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserLandListingController extends BaseLandController
{
    /**
     * عرض قوائم الأراضي الخاصة بالمستخدم
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = LandListing::where('user_id', $user->id)->with('user');

            // التصفية حسب نوع الأرض
            if ($request->has('land_type')) {
                $query->where('land_type', $request->land_type);
            }

            // التصفية حسب الغرض
            if ($request->has('purpose')) {
                $query->where('purpose', $request->purpose);
            }

            // الترتيب
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $landListings = $query->paginate($perPage);

            $formattedListings = $landListings->getCollection()->map(function ($listing) {
                return $this->formatLandListingData($listing);
            });

            return $this->successResponse([
                'land_listings' => $formattedListings,
                'pagination' => [
                    'current_page' => $landListings->currentPage(),
                    'last_page' => $landListings->lastPage(),
                    'per_page' => $landListings->perPage(),
                    'total' => $landListings->total(),
                ]
            ], 'تم جلب قوائم الأراضي الخاصة بك بنجاح');

        } catch (\Exception $e) {
            $this->logError($e, 'User land listings index error');
            return $this->errorResponse('حدث خطأ أثناء جلب قوائم الأراضي', 500);
        }
    }

    /**
     * عرض إحصائيات قوائم الأراضي الخاصة بالمستخدم
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $totalListings = LandListing::where('user_id', $user->id)->count();
            $forSaleCount = LandListing::where('user_id', $user->id)->where('purpose', 'بيع')->count();
            $forInvestmentCount = LandListing::where('user_id', $user->id)->where('purpose', 'استثمار')->count();

            $landTypeStats = LandListing::where('user_id', $user->id)
                ->selectRaw('land_type, COUNT(*) as count')
                ->groupBy('land_type')
                ->get()
                ->pluck('count', 'land_type');

            return $this->successResponse([
                'total_listings' => $totalListings,
                'for_sale_count' => $forSaleCount,
                'for_investment_count' => $forInvestmentCount,
                'land_type_statistics' => $landTypeStats,
            ], 'تم جلب الإحصائيات بنجاح');

        } catch (\Exception $e) {
            $this->logError($e, 'User land listings statistics error');
            return $this->errorResponse('حدث خطأ أثناء جلب الإحصائيات', 500);
        }
    }
}