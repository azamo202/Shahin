<?php

namespace App\Http\Controllers\User\AuthUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'user_type_id' => $request->user_type_id,
                'status' => User::STATUS_PENDING,
            ]);

            // إنشاء البيانات الإضافية بناءً على نوع المستخدم
            $this->createUserSpecificData($user, $request);

            // إرسال رسالة التحقق من البريد الإلكتروني
            $user->sendEmailVerificationNotification();

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الحساب بنجاح. يرجى التحقق من بريدك الإلكتروني وانتظار موافقة الإدارة.',
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();



            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء الحساب',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * إنشاء البيانات الإضافية بناءً على نوع المستخدم
     */
    private function createUserSpecificData(User $user, RegisterRequest $request): void
    {
        switch ($request->user_type_id) {
            case 2: // مالك أرض
                $user->landOwner()->create([
                    'national_id' => $request->national_id,
                ]);
                break;
            case 3: // وكيل شرعي
                $user->legalAgent()->create([
                    'agency_number' => $request->agency_number,
                    'national_id' => $request->national_id,
                ]);
                break;
            case 4: // منشأة تجارية
                $user->businessEntity()->create([
                    'business_name' => $request->business_name,
                    'commercial_register' => $request->commercial_register,
                    'national_id' => $request->national_id,
                    'commercial_file' => $request->file('commercial_register_file')->store('files', 'public'),
                ]);
                break;
            case 5: // وسيط عقاري
                $user->realEstateBroker()->create([
                    'national_id' => $request->national_id,
                    'license_number' => $request->license_number,
                    'license_file' => $request->file('license_file')->store('files', 'public'),
                ]);
                break;
            case 6: // شركة مزاد
                $user->auctionCompany()->create([
                    'auction_name' => $request->auction_name,
                    'national_id' => $request->national_id,
                    'commercial_register' => $request->commercial_register,
                    'commercial_file' => $request->file('commercial_register_file')->store('files', 'public'),
                    'license_number' => $request->license_number,
                    'license_file' => $request->file('license_file')->store('files', 'public'),
                ]);

                break;
        }
    }
}
