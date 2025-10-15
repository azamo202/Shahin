<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // المستخدم مسموح له يعدل ملفه الشخصي
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'phone'     => ['sometimes', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($userId)],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.string' => 'الاسم يجب أن يكون نصياً',
            'full_name.max'    => 'الاسم طويل جداً',
            'email.email'      => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique'     => 'البريد الإلكتروني مستخدم من قبل',
            'phone.string'     => 'رقم الهاتف يجب أن يكون نصياً',
            'phone.max'        => 'رقم الهاتف طويل جداً',
            'phone.unique'     => 'رقم الهاتف مستخدم من قبل',
        ];
    }
}
