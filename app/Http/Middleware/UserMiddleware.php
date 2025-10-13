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
        // تحقق من تسجيل الدخول
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        // تحقق أن هذا المستخدم ليس Admin
        if ($user instanceof \App\Models\Admin) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بالدخول هنا'
            ], 403);
        }

        // تحقق من حالة المستخدم
        if ($user->status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'حسابك غير مفعل بعد'
            ], 403);
        }

        return $next($request);
    }
}
