<?php

namespace App\Http\Controllers\User\Interested;

use App\Http\Controllers\Controller;
use App\Models\Interested;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class InterestedController extends Controller
{
    /**
     * تسجيل اهتمام جديد بعقار
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // التحقق من صحة البيانات المدخلة
            $validatedData = $this->validateRequest($request);
            
            // التحقق من وجود العقار ونشاطه
            $property = $this->getActiveProperty($validatedData['property_id']);
            if (!$property) {
                return $this->errorResponse('العقار غير متوفر حالياً.', 404);
            }

            // التحقق من التكرار
            if ($this->hasDuplicateInterest($validatedData)) {
                return $this->errorResponse('لقد سبق لك تسجيل الاهتمام بهذا العقار.', 409);
            }

            // إنشاء سجل الاهتمام
            $interested = $this->createInterestRecord($validatedData);
            
            DB::commit();

           /*
            $this->sendNotifications($interested, $property);
            */
            
            return $this->successResponse(
                $this->formatResponseData($interested),
                'تم تسجيل اهتمامك بالعقار بنجاح، وسنتواصل معك قريباً.',
                201
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse($e);
            
        } catch (\Exception $e) {
            Log::error('فشل في تسجيل الاهتمام بالعقار: ' . $e->getMessage(), [
                'property_id' => $request->property_id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('حدث خطأ غير متوقع أثناء تسجيل الاهتمام. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * التحقق من صحة البيانات
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'full_name'    => 'required|string|max:255|min:2',
            'phone'        => 'required|string|max:20|min:5',
            'email'        => 'required|email|max:255',
            'message'      => 'required|string|min:10|max:1000',
            'property_id'  => 'required|exists:properties,id',
        ], [
            'full_name.required' => 'الاسم الكامل مطلوب.',
            'full_name.min'      => 'الاسم الكامل يجب أن يكون على الأقل حرفين.',
            'phone.required'     => 'رقم الهاتف مطلوب.',
            'phone.min'          => 'رقم الهاتف يجب أن يكون على الأقل 10 أرقام.',
            'message.min'        => 'الرسالة يجب أن تكون على الأقل 10 أحرف.',
            'message.max'        => 'الرسالة يجب ألا تتجاوز 1000 حرف.',
        ]);
    }

    /**
     * الحصول على عقار نشط
     */
    private function getActiveProperty(int $propertyId): ?Property
    {
        return Property::where('id', $propertyId)
            ->where('status', 'مفتوح')
            ->first();
    }

    /**
     * التحقق من الاهتمام المكرر
     */
    private function hasDuplicateInterest(array $data): bool
    {
        $cacheKey = "interest_duplicate:{$data['email']}:{$data['property_id']}";
        
        if (Cache::has($cacheKey)) {
            return true;
        }

        $exists = Interested::where('email', $data['email'])
            ->where('property_id', $data['property_id'])
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($exists) {
            Cache::put($cacheKey, true, now()->addHours(24));
            return true;
        }

        Cache::put($cacheKey, true, now()->addMinutes(30));
        return false;
    }


    private function createInterestRecord(array $data): Interested
    {
        $user = Auth::user();

        return Interested::create([
            'full_name'   => $user?->full_name ?? $data['full_name'],
            'phone'       => $user?->phone ?? $data['phone'],
            'email'       => $user?->email ?? $data['email'],
            'message'     => $this->sanitizeMessage($data['message']),
            'user_id'     => $user?->id,
            'property_id' => $data['property_id'],
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }


    /**
     * تنظيف نص الرسالة
     */
    private function sanitizeMessage(string $message): string
    {
        return strip_tags($message);
    }

    /*
    private function sendNotifications(Interested $interest, Property $property): void
    {
        // إرسال إشعار للمسؤول
        $this->notifyAdmin($interest, $property);
        
       
        // تسجيل الحدث للنظام
        Log::info('تم تسجيل اهتمام جديد بالعقار', [
            'interest_id' => $interest->id,
            'property_id' => $property->id,
            'property_title' => $property->title,
            'user_email' => $interest->email,
            'submitted_at' => $interest->created_at
        ]);
    }
       
    
    private function notifyAdmin(Interested $interest, Property $property): void
    {
        // TODO: تنفيذ إشعار المسؤول (بريد، إشعار داخل النظام، etc.)
    }
    
    

    /**
     * تنسيق بيانات الاستجابة
     */
    private function formatResponseData(Interested $interested): array
    {
        return [
            'id' => $interested->id,
            'full_name' => $interested->full_name,
            'email' => $interested->email,
            'phone' => $interested->phone,
            'property_id' => $interested->property_id,
            'submitted_at' => $interested->created_at->toDateTimeString(),
            'reference_number' => 'INT-' . str_pad($interested->id, 6, '0', STR_PAD_LEFT)
        ];
    }

    /**
     * استجابة النجاح
     */
    private function successResponse(array $data, string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * استجابة الخطأ
     */
    private function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $code);
    }

    /**
     * استجابة أخطاء التحقق
     */
    private function validationErrorResponse(\Illuminate\Validation\ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'بيانات غير صالحة',
            'errors' => $e->errors(),
            'data' => null
        ], 422);
    }
}