<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // بيانات المستخدم الأساسية
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_type_id' => 'required|integer|exists:user_types,id',

            // رقم الهوية (مطلوب لكل الأنواع ما عدا user_type_id = 1)
            'national_id' => 'required_if:user_type_id,2,3,4,5,6|string|max:50|unique:land_owners,national_id',

            // وكيل شرعي (type 3)
            'agency_number' => 'required_if:user_type_id,3|string|max:100|unique:legal_agents,agency_number',

            // منشأة تجارية (type 4)
            'entity_name' => 'required_if:user_type_id,4,6|string|max:150',
            'commercial_register' => 'required_if:user_type_id,4|string|max:100|unique:business_entities,commercial_register',
            'commercial_register_file' => 'required_if:user_type_id,4,6|file|mimes:jpg,jpeg,png,pdf',

            // وسيط عقاري (type 5)
            'license_number' => 'required_if:user_type_id,5,6|string|max:100|unique:real_estate_brokers,license_number',
            'license_file' => 'required_if:user_type_id,5,6|file|mimes:jpg,jpeg,png,pdf',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'user_type_id.required' => 'نوع المستخدم مطلوب',
            'user_type_id.exists' => 'نوع المستخدم غير صحيح',

            'national_id.required_if' => 'رقم الهوية مطلوب',
            'national_id.unique' => 'رقم الهوية مستخدم من قبل',

            'agency_number.required_if' => 'رقم الوكالة مطلوب',
            'agency_number.unique' => 'رقم الوكالة مستخدم من قبل',

            'entity_name.required_if' => 'اسم المنشأة مطلوب',
            'commercial_register.required_if' => 'رقم السجل التجاري مطلوب',
            'commercial_register.unique' => 'رقم السجل التجاري مستخدم من قبل',
            'commercial_register_file.required_if' => 'ملف السجل التجاري مطلوب',

            'license_number.required_if' => 'رقم الترخيص مطلوب',
            'license_number.unique' => 'رقم الترخيص مستخدم من قبل',
            'license_file.required_if' => 'ملف الترخيص مطلوب',
        ];
    }
}
