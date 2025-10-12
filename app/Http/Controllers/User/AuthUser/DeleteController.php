<?php

namespace App\Http\Controllers\User\AuthUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\DeleteAccountRequest;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function destroy(DeleteAccountRequest $request)
    {
        $user = $request->user();

        // تحقق من كلمة المرور قبل الحذف
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'كلمة المرور غير صحيحة'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $user->tokens()->delete(); // حذف التوكنات
            $user->delete();            // حذف الحساب

            DB::commit();
            return response()->json([
                'message' => 'تم حذف الحساب بنجاح'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف الحساب',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
