<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LandListingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:مقبول,في قيد المراجعة,مرفوض,تم البيع',
            'rejection_reason' => 'required_if:status,مرفوض|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الأرض مطلوبة',
            'status.in' => 'الحالة المحددة غير صالحة',
            'rejection_reason.required_if' => 'سبب الرفض مطلوب عند رفض الأرض',
            'rejection_reason.max' => 'سبب الرفض يجب ألا يتجاوز 500 حرف'
        ];
    }
}