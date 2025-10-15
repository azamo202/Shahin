<?php

namespace App\Http\Controllers\User\AuthUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // تسجيل الدخول
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = User::with('userType')->where('email', $request->email)->first();

        if ($user->isPending()) {
            return response()->json(['message' => 'الحساب قيد المراجعة. يرجى الانتظار حتى الموافقة عليه.'], Response::HTTP_FORBIDDEN);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'يرجى التحقق من بريدك الإلكتروني أولاً'], Response::HTTP_FORBIDDEN);
        }

        $token = $user->createToken('auth-token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->userType?->type_name,
                'status' => $user->status,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(7)->toISOString()
        ], Response::HTTP_OK);
    }

    // تسجيل الخروج من الجهاز الحالي
    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح'], Response::HTTP_OK);
    }

    // تسجيل الخروج من جميع الأجهزة
    public function logoutAll(): JsonResponse
    {
        request()->user()->tokens()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج من جميع الأجهزة بنجاح'], Response::HTTP_OK);
    }
}
