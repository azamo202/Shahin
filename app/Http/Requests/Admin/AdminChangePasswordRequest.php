<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // السماح لجميع الأدمن المسجلين بتنفيذ الطلب
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'يرجى إدخال كلمة المرور الحالية.',
            'new_password.required' => 'يرجى إدخال كلمة المرور الجديدة.',
            'new_password.min' => 'كلمة المرور الجديدة يجب ألا تقل عن 6 أحرف.',
            'new_password.confirmed' => 'تأكيد كلمة المرور الجديدة غير متطابقة مع الأولى.',
        ];
    }
}
