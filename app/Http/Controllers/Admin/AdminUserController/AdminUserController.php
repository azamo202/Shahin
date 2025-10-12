<?php

namespace App\Http\Controllers\Admin\AdminUserController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // عرض جميع المستخدمين
    public function index()
    {
        $users = User::with(['userType'])->get()->map(function ($user) {

            $data = [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'user_type' => $user->userType->type_name ?? 'غير محدد',
                'created_at' => $user->created_at,
            ];

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
        });


        return response()->json($users);
    }



    // عرض المستخدمين المقبولين
    public function approved()
    {
        $users = User::where('status', 'approved')->get();
        return response()->json($users);
    }

    // عرض المستخدمين قيد المعالجة (pending)
    public function pending()
    {
        $users = User::where('status', 'pending')->get();
        return response()->json($users);
    }

    // قبول مستخدم
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'approved';
        $user->save();

        return response()->json([
            'message' => 'تم قبول المستخدم بنجاح',
            'user' => $user
        ]);
    }

    // رفض مستخدم
    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'rejected';
        $user->save();

        return response()->json([
            'message' => 'تم رفض المستخدم بنجاح',
            'user' => $user
        ]);
    }
}
