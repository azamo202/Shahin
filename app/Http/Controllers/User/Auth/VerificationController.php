<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'رابط التحقق غير صحيح'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'البريد مؤكد سابقاً'], 200);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        // رد JSON مناسب للـ SPA أو للموبايل
        return response()->json(['message' => 'تم التحقق بنجاح'], 200);
    }
    // في الكنترولر:
    public function resend(Request $request)
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'البريد مؤكد بالفعل'], 400);
        }
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'تم إرسال رابط التحقق مجدداً'], 200);
    }
}
