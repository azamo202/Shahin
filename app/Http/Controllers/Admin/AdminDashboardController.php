<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Auction;
use App\Models\Interested;
use App\Models\Property;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * إحصائيات كاملة ومفصلة للداش بورد
     */
    public function statistics(): JsonResponse
    {
        try {
            // إحصائيات المستخدمين - تفصيلية مع الرسوم البيانية
            $usersCounts = User::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $usersStats = [
                'total' => array_sum($usersCounts),
                'approved' => $usersCounts['approved'] ?? 0,
                'pending' => $usersCounts['pending'] ?? 0,
                'rejected' => $usersCounts['rejected'] ?? 0,
                'weekly_registrations' => $this->getWeeklyRegistrations(User::class),
                'monthly_trend' => $this->getMonthlyTrend(User::class),
            ];

            // إحصائيات المزادات - تفصيلية
            $auctionsCounts = Auction::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $auctionsStats = [
                'total' => array_sum($auctionsCounts),
                'active' => $auctionsCounts['active'] ?? 0,
                'completed' => $auctionsCounts['completed'] ?? 0,
                'cancelled' => $auctionsCounts['cancelled'] ?? 0,
                'pending' => $auctionsCounts['pending'] ?? 0,
                'weekly_auctions' => $this->getWeeklyRegistrations(Auction::class),
                'status_distribution' => $auctionsCounts,
            ];

            // إحصائيات الاهتمامات - تفصيلية مع تحليل
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
                'recent_month' => Interested::where('created_at', '>=', now()->subDays(30))->count(),
                'daily_interests' => $this->getDailyInterests(),
                'status_chart' => $interestsCounts,
            ];

            // إحصائيات العقارات - تفصيلية
            $propertiesCounts = Property::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $propertiesStats = [
                'total' => array_sum($propertiesCounts),
                'accepted' => $propertiesCounts['مقبول'] ?? 0,
                'rejected' => $propertiesCounts['مرفوض'] ?? 0,
                'pending' => $propertiesCounts['قيد المراجعة'] ?? 0,
                'weekly_properties' => $this->getWeeklyRegistrations(Property::class),
                'status_distribution' => $propertiesCounts,
            ];

            // إحصائيات عامة للنظام
            $generalStats = [
                'total_revenue' => $this->calculateTotalRevenue(),
                'active_auctions' => Auction::where('status', 'active')->count(),
                'pending_requests' => $usersCounts['pending'] ?? 0 + ($propertiesCounts['قيد المراجعة'] ?? 0),
                'today_registrations' => User::whereDate('created_at', today())->count(),
                'system_health' => $this->calculateSystemHealth(),
            ];

            // بيانات للرسوم البيانية
            $chartsData = [
                'users_registration_chart' => $this->getRegistrationChart(),
                'auctions_status_chart' => $auctionsCounts,
                'interests_status_chart' => $interestsCounts,
                'properties_status_chart' => $propertiesCounts,
                'monthly_activity' => $this->getMonthlyActivity(),
            ];

            // تجميع كل الإحصائيات
            $data = [
                'users' => $usersStats,
                'auctions' => $auctionsStats,
                'interests' => $interestsStats,
                'properties' => $propertiesStats,
                'general' => $generalStats,
                'charts' => $chartsData,
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

    /**
     * الحصول على التسجيلات الأسبوعية
     */
    private function getWeeklyRegistrations($model)
    {
        return $model::where('created_at', '>=', now()->subDays(7))
            ->count();
    }

    /**
     * الحصول على الاتجاه الشهري
     */
    private function getMonthlyTrend($model)
    {
        $currentMonth = $model::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = $model::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        return [
            'current_month' => $currentMonth,
            'last_month' => $lastMonth,
            'growth' => $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2) : 0,
        ];
    }

    /**
     * الحصول على الاهتمامات اليومية
     */
    private function getDailyInterests()
    {
        return Interested::whereDate('created_at', today())
            ->count();
    }

    /**
     * حساب الإيرادات الإجمالية
     */
    private function calculateTotalRevenue()
    {
        // يمكن تعديل هذا حسب منطق الإيرادات في نظامك
        return Auction::where('status', 'completed')
            ->count() * 1000; // مثال: 1000 ريال لكل مزاد مكتمل
    }

    /**
     * حساب صحة النظام
     */
    private function calculateSystemHealth()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'approved')->count();
        
        $totalAuctions = Auction::count();
        $activeAuctions = Auction::where('status', 'active')->count();
        
        $healthScore = (
            ($activeUsers / max($totalUsers, 1)) * 40 +
            ($activeAuctions / max($totalAuctions, 1)) * 40 +
            (min(Interested::whereDate('created_at', today())->count() / 10, 1)) * 20
        );

        return round($healthScore, 2);
    }

    /**
     * الحصول على رسم بياني للتسجيلات
     */
    private function getRegistrationChart()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[$date] = User::whereDate('created_at', $date)->count();
        }

        return $data;
    }

    /**
     * الحصول على النشاط الشهري
     */
    private function getMonthlyActivity()
    {
        $activity = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M');
            
            $activity[$monthName] = [
                'users' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'auctions' => Auction::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
                'interests' => Interested::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        }

        return $activity;
    }
}