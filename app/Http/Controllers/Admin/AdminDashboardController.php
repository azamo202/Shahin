<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Auction;
use App\Models\Interested;
use App\Models\Property;

class AdminDashboardController extends Controller
{
    /**
     * إحصائيات كاملة للداش بورد
     */
    public function statistics(): JsonResponse
    {
        try {
            // إحصائيات المستخدمين - استعلام واحد بدلاً من عدة استعلامات
            $usersCounts = User::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $usersStats = [
                'total' => array_sum($usersCounts),
                'approved' => $usersCounts['approved'] ?? 0,
                'pending' => $usersCounts['pending'] ?? 0,
                'rejected' => $usersCounts['rejected'] ?? 0,
            ];

            // إحصائيات المزادات
            $auctionsStats = Auction::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // إحصائيات الاهتمامات - استعلام واحد مع حسابات إضافية
            $interestsCounts = Interested::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $interestsStats = [
                'total' => array_sum($interestsCounts),
                'pending' => $interestsCounts['قيد المراجعة'] ?? 0,
                'reviewed' => $interestsCounts['تمت المراجعة'] ?? 0,
                'contacted' => $interestsCounts['تم التواصل'] ?? 0,
                'cancelled' => $interestsCounts['ملغي'] ?? 0,
                'recent_week' => Interested::where('created_at', '>=', now()->subDays(7))->count(),
            ];

            // إحصائيات العقارات - استعلام واحد
            $propertiesCounts = Property::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $propertiesStats = [
                'total' => array_sum($propertiesCounts),
                'accepted' => $propertiesCounts['مقبول'] ?? 0,
                'rejected' => $propertiesCounts['مرفوض'] ?? 0,
                'pending' => $propertiesCounts['قيد المراجعة'] ?? 0,
            ];

            // تجميع كل الإحصائيات
            $data = [
                'users' => $usersStats,
                'auctions' => $auctionsStats,
                'interests' => $interestsStats,
                'properties' => $propertiesStats,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'تم جلب الإحصائيات بنجاح'
            ], 200);

        } catch (\Exception $e) {
            Log::error('فشل في جلب إحصائيات الداش بورد: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}