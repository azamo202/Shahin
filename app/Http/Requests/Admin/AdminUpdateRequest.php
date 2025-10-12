<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AdminUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId = $this->route('admin');

        return [
            'full_name' => 'sometimes|string|max:150',
            'email' => 'sometimes|email|max:150|unique:admins,email,' . $adminId,
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.max' => 'الاسم الكامل يجب ألا يتجاوز 150 حرفاً',
            'email.email' => 'صيغة البريد الإلكتروني غير صالحة',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 150 حرفاً',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 20 رقماً',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}