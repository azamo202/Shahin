<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // تحقق من تسجيل دخول الأدمن
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'message' => 'غير مصرح لك بالدخول'
            ], 403);
        }

        return $next($request);
    }
}
