<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|current_password',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'كلمة المرور مطلوبة للتأكيد',
            'password.current_password' => 'كلمة المرور غير صحيحة',
        ];
    }
}