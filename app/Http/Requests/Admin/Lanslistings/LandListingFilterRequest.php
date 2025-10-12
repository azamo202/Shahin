<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LandListingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'land_type' => 'sometimes|string|in:سكني,تجاري,زراعي,صناعي',
            'purpose' => 'sometimes|string|in:بيع,إيجار',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|string|in:مقبول,في قيد المراجعة,مرفوض,تم البيع',
            'min_area' => 'sometimes|numeric|min:0',
            'max_area' => 'sometimes|numeric|min:0|gt:min_area',
            'sort_by' => 'sometimes|string|in:created_at,updated_at,area,price',
            'sort_order' => 'sometimes|string|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:100'
        ];
    }

    public function messages(): array
    {
        return [
            'land_type.in' => 'نوع الأرض غير صالح',
            'purpose.in' => 'الغرض غير صالح',
            'max_area.gt' => 'الحد الأقصى للمساحة يجب أن يكون أكبر من الحد الأدنى'
        ];
    }
}