<?php

namespace App\Http\Controllers\User\Auctions;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicAuctionController extends Controller
{
    /**
     * عرض قائمة المزادات العامة
     */
    public function index(Request $request)
    {
        // بناء الاستعلام الأساسي للمزادات المفتوحة فقط
        $query = Auction::where('status', 'open')
            ->with(['user', 'images'])
            ->orderBy('auction_date', 'desc');

        // البحث بالكلمة المفتاحية
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            });
        }

        // الفلترة بحسب التاريخ
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('auction_date', $request->date);
        }

        // الفلترة بحسب المنطقة
        if ($request->has('region') && !empty($request->region)) {
            $query->where('address', 'LIKE', "%{$request->region}%");
        }

        // التقسيم إلى صفحات
        $auctions = $query->paginate(12);

        return view('auctions.index', compact('auctions'));
    }

    /**
     * عرض تفاصيل مزاد معين
     */
    public function show($id)
    {
        $auction = Auction::where('status', 'open')
            ->with(['user', 'images', 'videos'])
            ->findOrFail($id);

        return view('auctions.show', compact('auction'));
    }

    /**
     * البحث المتقدم في المزادات
     */
    public function search(Request $request)
    {
        $query = Auction::where('status', 'open')
            ->with(['user', 'images']);

        // تطبيق جميع عوامل التصفية
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('auction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('auction_date', '<=', $request->end_date);
        }

        if ($request->filled('region')) {
            $query->where('address', 'LIKE', "%{$request->region}%");
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort_by', 'auction_date');
        $sortOrder = $request->get('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        $auctions = $query->paginate(12)->appends($request->all());

        return view('auctions.search', compact('auctions'));
    }
}
