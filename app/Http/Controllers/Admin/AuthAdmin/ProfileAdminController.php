<?php

namespace App\Http\Controllers\Admin\AuthAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;
use App\Http\Requests\Admin\AdminUpdateRequest;

class ProfileAdminController extends Controller
{
    // عرض بيانات المدير الحالي
    public function profile(Request $request): JsonResponse
    {
        try {
            $admin = $request->user();
            return response()->json([
                'status'=>true,
                'data'=>[
                    'id'=>$admin->id,
                    'full_name'=>$admin->full_name,
                    'email'=>$admin->email,
                    'phone'=>$admin->phone,
                    'created_at'=>$admin->created_at,
                    'updated_at'=>$admin->updated_at,
                ]
            ],200);
        } catch (\Exception $e) {
            Log::error('Admin profile error: ' . $e->getMessage());
            return response()->json(['status'=>false,'message'=>'حدث خطأ أثناء جلب البيانات'],500);
        }
    }

    // تحديث بيانات المدير
    public function updateProfile(AdminUpdateRequest $request): JsonResponse
    {
        try {
            $admin = $request->user();
            $validated = $request->validated();
            $admin->update($validated);

            return response()->json([
                'status'=>true,
                'message'=>'تم تحديث البيانات بنجاح',
                'data'=>[
                    'id'=>$admin->id,
                    'full_name'=>$admin->full_name,
                    'email'=>$admin->email,
                    'phone'=>$admin->phone,
                ]
            ],200);
        } catch (\Exception $e) {
            Log::error('Admin update error: ' . $e->getMessage());
            return response()->json(['status'=>false,'message'=>'حدث خطأ أثناء تحديث البيانات'],500);
        }
    }

    // حذف الحساب
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $admin = $request->user();
            $admin->tokens()->delete();
            $admin->delete();

            return response()->json(['status'=>true,'message'=>'تم حذف الحساب بنجاح'],200);
        } catch (\Exception $e) {
            Log::error('Admin delete account error: ' . $e->getMessage());
            return response()->json(['status'=>false,'message'=>'حدث خطأ أثناء حذف الحساب'],500);
        }
    }
}
