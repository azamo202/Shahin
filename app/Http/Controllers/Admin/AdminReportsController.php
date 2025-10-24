<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Property;
use App\Models\Auction;
use App\Models\Interested;

class AdminReportsController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'users');
        $period = $request->get('period', 'daily');

        $dateRange = $this->getDateRange($period);

        switch ($type) {
            case 'properties':
                $data = $this->getPropertiesReport($request, $dateRange);
                $message = 'تقرير العقارات';
                break;

            case 'auctions':
                $data = $this->getAuctionsReport($request, $dateRange);
                $message = 'تقرير المزادات';
                break;

            case 'interests':
                $data = $this->getInterestsReport($request, $dateRange);
                $message = 'تقرير طلبات الاهتمام';
                break;

            case 'users':
            default:
                $data = $this->getUsersReport($request, $dateRange);
                $message = 'تقرير المستخدمين';
                break;
        }

        return response()->json([
            'success' => true,
            'period' => $period,
            'range' => [
                'from' => $dateRange['from']->toDateTimeString(),
                'to' => $dateRange['to']->toDateTimeString(),
            ],
            'count' => count($data),
            'data' => $data,
            'message' => "تم جلب {$message} ({$period}) بنجاح"
        ]);
    }

    /**
     * تحديد النطاق الزمني حسب المدة المطلوبة
     */
    private function getDateRange(string $period): array
    {
        $now = Carbon::now('Asia/Riyadh'); // استخدام توقيت السعودية

        return match ($period) {
            'daily' => [
                'from' => $now->copy()->startOfDay(),
                'to' => $now->copy()->endOfDay()
            ],
            'weekly' => [
                'from' => $now->copy()->startOfWeek(),
                'to' => $now->copy()->endOfWeek()
            ],
            'monthly' => [
                'from' => $now->copy()->startOfMonth(),
                'to' => $now->copy()->endOfMonth()
            ],
            default => [
                'from' => $now->copy()->startOfDay(),
                'to' => $now->copy()->endOfDay()
            ]
        };
    }

    /**
     * 🧾 تقرير المستخدمين
     */
    private function getUsersReport(Request $request, array $dateRange)
    {
        $query = User::with('userType')
            ->whereBetween('created_at', [
                $dateRange['from']->setTimezone('UTC'),
                $dateRange['to']->setTimezone('UTC')
            ]);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('full_name', 'LIKE', "%{$request->search}%");
        }

        return $query->get()->map(fn($user) => [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'status' => $user->status,
            'user_type' => $user->userType->type_name ?? 'غير محدد',
            'created_at' => $user->created_at->format('Y-m-d H:i'),
        ]);
    }

    /**
     * 🏠 تقرير العقارات
     */
    private function getPropertiesReport(Request $request, array $dateRange)
    {
        $query = Property::with('user')
            ->whereBetween('created_at', [
                $dateRange['from']->setTimezone('UTC'),
                $dateRange['to']->setTimezone('UTC')
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        return $query->get()->map(fn($property) => [
            'id' => $property->id,
            'title' => $property->title,
            'region' => $property->region,
            'city' => $property->city,
            'status' => $property->status,
            'owner' => $property->user->full_name ?? 'غير محدد',
            'price_per_meter' => $property->price_per_meter,
            'created_at' => $property->created_at->format('Y-m-d H:i'),
        ]);
    }

    /**
     * 🔔 تقرير طلبات الاهتمام
     */
    private function getInterestsReport(Request $request, array $dateRange)
    {
        $query = Interested::with(['user', 'property'])
            ->whereBetween('created_at', [
                $dateRange['from']->setTimezone('UTC'),
                $dateRange['to']->setTimezone('UTC')
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->get()->map(fn($interest) => [
            'id' => $interest->id,
            'user' => $interest->user->full_name ?? 'غير محدد',
            'email' => $interest->user->email ?? 'غير متوفر',
            'property' => $interest->property->title ?? 'غير محدد',
            'status' => $interest->status,
            'created_at' => $interest->created_at->format('Y-m-d H:i'),
        ]);
    }

    /**
     * 🕋 تقرير المزادات
     */
    private function getAuctionsReport(Request $request, array $dateRange)
    {
        $query = Auction::with(['company.user'])
            ->whereBetween('auction_date', [
                $dateRange['from']->setTimezone('UTC'),
                $dateRange['to']->setTimezone('UTC')
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->get()->map(fn($auction) => [
            'id' => $auction->id,
            'title' => $auction->title,
            'status' => $auction->status,
            'auction_date' => $auction->auction_date,
            'company' => $auction->company->auction_name ?? 'غير محدد',
            'owner' => $auction->company->user->full_name ?? 'غير محدد',
            'created_at' => $auction->created_at->format('Y-m-d H:i'),
        ]);
    }
}
