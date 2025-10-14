<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. التحقق من تسجيل الدخول
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        // 2. منع دخول الأدمن
        if ($user instanceof \App\Models\Admin) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بالدخول هنا'
            ], 403);
        }

        // 3. التحقق من تفعيل الحساب
        if ($user->status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'حسابك غير مفعل بعد'
            ], 403);
        }

        // 4. التحقق من نوع المستخدم (الصلاحية)
        $allowedRoles = [2, 3, 4, 5];

        // التحقق من نوع المستخدم عبر العلاقة
        if (!in_array($user->user_type_id, $allowedRoles)) {
            return response()->json([
                'status' => false,
                'message' => 'ليس لديك صلاحية للوصول'
            ], 403);
        }

        return $next($request);
    }
}
