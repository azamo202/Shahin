<?php

namespace App\Http\Controllers\User\AuthUser;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PasswordController extends Controller
{
    /**
     * تغيير كلمة المرور للمستخدم الحالي
     */
    public function change(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        // التحقق من صحة كلمة المرور الحالية
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'كلمة المرور الحالية غير صحيحة'
            ], Response::HTTP_FORBIDDEN);
        }

        // منع استخدام نفس كلمة المرور القديمة
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'لا يمكن استخدام نفس كلمة المرور القديمة'
            ], Response::HTTP_BAD_REQUEST);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'تم تحديث كلمة المرور بنجاح'
        ], Response::HTTP_OK);
    }
}
