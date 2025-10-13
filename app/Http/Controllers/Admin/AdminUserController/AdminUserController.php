<?php

namespace App\Http\Controllers\Admin\AdminUserController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * عرض جميع المستخدمين مع التصفية
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['userType']);

        // التصفية بالاسم
        if ($request->has('search') && !empty($request->search)) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        // التصفية بالحالة
        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // التصفية بنوع المستخدم
        if ($request->has('user_type_id') && !empty($request->user_type_id)) {
            $query->where('user_type_id', $request->user_type_id);
        }

        $users = $query->get()->map(function ($user) {
            return $this->formatUserData($user);
        });

        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count()
        ]);
    }

    /**
     * عرض المستخدمين المقبولين مع التصفية
     */
    public function approved(Request $request): JsonResponse
    {
        $query = User::with(['userType'])->where('status', 'approved');

        // التصفية بالاسم
        if ($request->has('search') && !empty($request->search)) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        $users = $query->get()->map(function ($user) {
            return $this->formatUserData($user);
        });

        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count()
        ]);
    }

    /**
     * عرض المستخدمين قيد المعالجة مع التصفية
     */
    public function pending(Request $request): JsonResponse
    {
        $query = User::with(['userType'])->where('status', 'pending');

        // التصفية بالاسم
        if ($request->has('search') && !empty($request->search)) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        $users = $query->get()->map(function ($user) {
            return $this->formatUserData($user);
        });

        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count()
        ]);
    }

    /**
     * عرض المستخدمين المرفوضين مع التصفية
     */
    public function rejected(Request $request): JsonResponse
    {
        $query = User::with(['userType'])->where('status', 'rejected');

        // التصفية بالاسم
        if ($request->has('search') && !empty($request->search)) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        $users = $query->get()->map(function ($user) {
            return $this->formatUserData($user);
        });

        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count()
        ]);
    }

    /**
     * قبول مستخدم
     */
    public function approve($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->status = 'approved';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم قبول المستخدم بنجاح',
            'data' => $this->formatUserData($user)
        ]);
    }

    /**
     * رفض مستخدم
     */
    public function reject($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->status = 'rejected';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم رفض المستخدم بنجاح',
            'data' => $this->formatUserData($user)
        ]);
    }

    /**
     * تحديث حالة المستخدم
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,pending,rejected'
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة المستخدم بنجاح',
            'data' => $this->formatUserData($user)
        ]);
    }

    /**
     * تنسيق بيانات المستخدم
     */
    private function formatUserData(User $user): array
    {
        $data = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'user_type' => $user->userType->type_name ?? 'غير محدد',
            'created_at' => $user->created_at,
        ];

        // إضافة التفاصيل حسب نوع المستخدم
        if ($user->landOwner) {
            $data['details'] = $user->landOwner;
        } elseif ($user->legalAgent) {
            $data['details'] = $user->legalAgent;
        } elseif ($user->businessEntity) {
            $data['details'] = $user->businessEntity;
        } elseif ($user->realEstateBroker) {
            $data['details'] = $user->realEstateBroker;
        } elseif ($user->auctionCompany) {
            $data['details'] = $user->auctionCompany;
        } else {
            $data['details'] = null;
        }

        return $data;
    }

    /**
     * إحصائيات المستخدمين
     */
    public function statistics(): JsonResponse
    {
        $total = User::count();
        $approved = User::where('status', 'approved')->count();
        $pending = User::where('status', 'pending')->count();
        $rejected = User::where('status', 'rejected')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected
            ]
        ]);
    }
}