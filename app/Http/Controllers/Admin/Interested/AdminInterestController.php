<?php

namespace App\Http\Controllers\Admin\Interested;

use App\Http\Controllers\Controller;
use App\Models\Interested;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminInterestController extends Controller
{
    /**
     * جلب جميع طلبات الاهتمام مع إمكانيات الفلترة والترتيب
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Interested::with(['property' => function ($query) {
                $query->select('id', 'title');
            }])->with(['user' => function ($query) {
                $query->select('id', 'full_name');
            }]);

            // تطبيق الفلتر حسب الحالة
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // فلترة حسب العقار
            if ($request->has('property_id') && $request->property_id) {
                $query->where('property_id', $request->property_id);
            }

            // فلترة حسب التاريخ
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // البحث حسب الاسم أو البريد
            if ($request->has('search') && $request->search) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('full_name', 'LIKE', $searchTerm)
                        ->orWhere('email', 'LIKE', $searchTerm)
                        ->orWhere('phone', 'LIKE', $searchTerm)
                        ->orWhereHas('property', function ($q) use ($searchTerm) {
                            $q->where('title', 'LIKE', $searchTerm);
                        });
                });
            }

            // الترتيب
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // التقسيم (Pagination)
            $perPage = $request->get('per_page', 15);
            $interests = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'interests' => $interests->items(),
                    'pagination' => [
                        'current_page' => $interests->currentPage(),
                        'per_page' => $interests->perPage(),
                        'total' => $interests->total(),
                        'last_page' => $interests->lastPage(),
                    ],
                    'filters' => [
                        'status_options' => $this->getStatusOptions(),
                        'properties' => Property::select('id', 'title')->get(),
                    ]
                ],
                'message' => 'تم جلب طلبات الاهتمام بنجاح.'
            ]);
        } catch (\Exception $e) {
            Log::error('فشل في جلب طلبات الاهتمام: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);


            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب طلبات الاهتمام.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض تفاصيل طلب اهتمام محدد
     */
    public function show($id): JsonResponse
    {
        try {
            // نحمل فقط العقار المرتبط بدون المستخدم
            $interest = Interested::with('property')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $interest,
                'message' => 'تم جلب تفاصيل الاهتمام بنجاح.'
            ]);
        } catch (\Exception $e) {
            Log::error('فشل في جلب تفاصيل الاهتمام: ' . $e->getMessage(), [
                'interest_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على طلب الاهتمام.'
            ], 404);
        }
    }


    /**
     * تحديث حالة طلب الاهتمام
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'status' => 'required|in:قيد المراجعة,تمت المراجعة,تم التواصل,ملغي',
                'admin_notes' => 'nullable|string|max:500'
            ]);

            $interest = Interested::findOrFail($id);

            $oldStatus = $interest->status;
            $interest->status = $request->status;

            // تحديث الملاحظات سواء كانت موجودة أو null
            if ($request->has('admin_notes')) {
                $interest->admin_notes = $request->admin_notes;
            }

            $interest->save();

            DB::commit();
            Log::info('تم تحديث حالة طلب الاهتمام', [
                'interest_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $interest,
                'message' => 'تم تحديث حالة الاهتمام بنجاح.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('فشل تحديث حالة الاهتمام: ' . $e->getMessage(), [
                'interest_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة.'
            ], 500);
        }
    }

    /**
     * حذف طلب اهتمام
     */
    public function destroy($id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $interest = Interested::findOrFail($id);
            $interestData = $interest->toArray();
            $interest->delete();

            DB::commit();
            Log::info('تم حذف طلب الاهتمام', [
                'interest_id' => $id,
                'interest_data' => $interestData,
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الاهتمام بنجاح.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('فشل حذف الاهتمام: ' . $e->getMessage(), [
                'interest_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الاهتمام.'
            ], 500);
        }
    }

    /**
     * الحصول على إحصائيات طلبات الاهتمام
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $total = Interested::count();
            $pending = Interested::where('status', 'قيد المراجعة')->count();
            $reviewed = Interested::where('status', 'تمت المراجعة')->count();
            $contacted = Interested::where('status', 'تم التواصل')->count();
            $cancelled = Interested::where('status', 'ملغي')->count();

            $recentCount = Interested::where('created_at', '>=', now()->subDays(7))->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'pending' => $pending,
                    'reviewed' => $reviewed,
                    'contacted' => $contacted,
                    'cancelled' => $cancelled,
                    'recent_week' => $recentCount,
                ],
                'message' => 'تم جلب الإحصائيات بنجاح.'
            ]);
        } catch (\Exception $e) {
            Log::error('فشل في جلب إحصائيات الاهتمامات: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الإحصائيات.'
            ], 500);
        }
    }

    /**
     * الحصول على خيارات الحالات المتاحة
     */
    private function getStatusOptions(): array
    {
        return [
            ['value' => 'قيد المراجعة', 'label' => 'قيد المراجعة'],
            ['value' => 'تمت المراجعة', 'label' => 'تمت المراجعة'],
            ['value' => 'تم التواصل', 'label' => 'تم التواصل'],
            ['value' => 'ملغي', 'label' => 'ملغي'],
        ];
    }
}
