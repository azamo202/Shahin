<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * السماح فقط للمستخدم المصادق عليه
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:6',     // طول بسيط (تقدر تزيده أو تحذفه)
                'confirmed', // يتحقق من وجود new_password_confirmation
            ],
        ];
    }

    /**
     * رسائل مخصصة
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'كلمة المرور الحالية مطلوبة',
            'new_password.required'     => 'كلمة المرور الجديدة مطلوبة',
            'new_password.min'          => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
            'new_password.confirmed'    => 'تأكيد كلمة المرور لا يطابق الجديدة',
        ];
    }
}
