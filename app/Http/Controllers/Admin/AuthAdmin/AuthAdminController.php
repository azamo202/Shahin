<?php

namespace App\Http\Controllers\Admin\AuthAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Http\Requests\Admin\AdminRegisterRequest;
use App\Http\Requests\Admin\AdminChangePasswordRequest;

class AuthAdminController extends Controller
{
    // تسجيل مدير جديد
    public function register(AdminRegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $admin = Admin::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
            ]);

            $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'data' => [
                    'admin' => [
                        'id' => $admin->id,
                        'full_name' => $admin->full_name,
                        'email' => $admin->email,
                        'phone' => $admin->phone,
                    ],
                    'token' => $token
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Admin registration error: ' . $e->getMessage());
            return response()->json(['status'=>false,'message'=>'حدث خطأ أثناء إنشاء الحساب'], 500);
        }
    }

    // تسجيل الدخول
    public function login(AdminLoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $admin = Admin::where('email', $validated['email'])->first();

            if (!$admin || !Hash::check($validated['password'], $admin->password)) {
                return response()->json(['status'=>false,'message'=>'بيانات الدخول غير صحيحة'], 401);
            }

            $admin->tokens()->delete();
            $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

            return response()->json([
                'status'=>true,
                'message'=>'تم تسجيل الدخول بنجاح',
                'data'=>[
                    'admin'=>[
                        'id'=>$admin->id,
                        'full_name'=>$admin->full_name,
                        'email'=>$admin->email,
                        'phone'=>$admin->phone,
                    ],
                    'token'=>$token
                ]
            ],200);
        } catch (\Exception $e) {
            Log::error('Admin login error: ' . $e->getMessage());
            return response()->json(['status'=>false,'message'=>'حدث خطأ أثناء تسجيل الدخول'], 500);
        }
    }

    // تسجيل الخروج
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['status'=>true,'message'=>'تم تسجيل الخروج بنجاح'],200);
        } catch (\Exception $e) {
            Log::error('Admin logout error: ' . $e->getMessage());
            return response()->json(['status'=>false,'message'=>'حدث خطأ أثناء تسجيل الخروج'],500);
        }
    }

    // تغيير كلمة المرور
    public function changePassword(AdminChangePasswordRequest $request): JsonResponse
    {
        try {
            $admin = $request->user();

            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json(['status'=>false,'message'=>'كلمة المرور الحالية غير صحيحة'],422);
            }

            $admin->update(['password'=>Hash::make($request->new_password)]);

            return response()->json(['status'=>true,'message'=>'تم تغيير كلمة المرور بنجاح'],200);
        } catch (\Exception $e) {
            Log::error('Admin change password error: ' . $e->getMessage());
            return response()->json(['status'=>false,'message'=>'حدث خطأ أثناء تغيير كلمة المرور'],500);
        }
    }
}
