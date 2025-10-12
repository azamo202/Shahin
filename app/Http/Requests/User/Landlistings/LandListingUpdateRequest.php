<?php

namespace App\Http\Requests\User\Landlistings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LandListingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:150',
            'land_type' => 'sometimes|in:زراعي,استثماري,سكني',
            'location' => 'sometimes|string|max:255',
            'area' => 'sometimes|numeric|min:0.01',
            'description' => 'sometimes|string',
            'deed_image' => 'sometimes|string',
            'purpose' => 'sometimes|in:بيع,استثمار',
            'price_per_meter' => 'nullable|numeric|min:0',
            'investment_duration_from' => 'nullable|integer|min:1',
            'investment_duration_to' => 'nullable|integer|min:1',
            'investment_estimated_value' => 'nullable|numeric|min:0',
            'real_estate_announcement_no' => 'nullable|string|max:100',
            'no_dispute_confirmed' => 'sometimes|boolean|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'عنوان الإعلان يجب ألا يتجاوز 150 حرفاً',
            'land_type.in' => 'نوع الأرض يجب أن يكون زراعي، استثماري، أو سكني',
            'location.max' => 'الموقع يجب ألا يتجاوز 255 حرفاً',
            'area.numeric' => 'المساحة يجب أن تكون رقماً',
            'area.min' => 'المساحة يجب أن تكون أكبر من الصفر',
            'purpose.in' => 'الغرض يجب أن يكون بيع أو استثمار',
            'price_per_meter.numeric' => 'سعر المتر يجب أن يكون رقماً',
            'price_per_meter.min' => 'سعر المتر يجب أن يكون أكبر من الصفر',
            'investment_duration_from.integer' => 'مدة الاستثمار يجب أن تكون رقماً صحيحاً',
            'investment_duration_from.min' => 'مدة الاستثمار يجب أن تكون سنة على الأقل',
            'investment_duration_to.integer' => 'مدة الاستثمار يجب أن تكون رقماً صحيحاً',
            'investment_duration_to.min' => 'مدة الاستثمار يجب أن تكون سنة على الأقل',
            'investment_estimated_value.numeric' => 'القيمة التقريبية للاستثمار يجب أن تكون رقماً',
            'investment_estimated_value.min' => 'القيمة التقريبية للاستثمار يجب أن تكون أكبر من الصفر',
            'real_estate_announcement_no.max' => 'رقم الإعلان العقاري يجب ألا يتجاوز 100 حرف',
            'no_dispute_confirmed.accepted' => 'يجب الموافقة على تعهد عدم وجود نزاع',
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