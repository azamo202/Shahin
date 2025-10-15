<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserType;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // التحقق من تسجيل الدخول
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        // لو لم يتم تمرير أي أدوار، اسمح للجميع
        if (empty($roles)) {
            return $next($request);
        }

        // جلب IDs للأدوار المطلوبة
        $allowedRoleIds = UserType::whereIn('type_name', $roles)
            ->pluck('id')
            ->toArray();

        // التحقق من دور المستخدم
        if (!in_array($user->user_type_id, $allowedRoleIds)) {
            return response()->json([
                'status' => false,
                'message' => 'ليس لديك صلاحية للوصول'
            ], 403);
        }

        return $next($request);
    }
}
