<?php

namespace App\Http\Controllers\User\AuthUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    /**
     * الحصول على بيانات المستخدم الحالي
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('userType');

        return response()->json([
            'user' => [
                'id'        => $user->id,
                'full_name' => $user->full_name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'user_type' => $user->userType?->type_name,
                'status'    => $user->status,
            ]
        ], Response::HTTP_OK);
    }

    /**
     * تحديث بيانات الملف الشخصي
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $user->update($request->validated());

            return response()->json([
                'message' => 'تم تحديث البيانات بنجاح',
                'user' => [
                    'id'        => $user->id,
                    'full_name' => $user->full_name,
                    'email'     => $user->email,
                    'phone'     => $user->phone,
                    'user_type' => $user->userType?->type_name,
                    'status'    => $user->status,
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'حدث خطأ أثناء تحديث البيانات',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
