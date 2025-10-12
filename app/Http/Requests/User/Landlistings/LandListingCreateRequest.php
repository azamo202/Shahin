<?php

namespace App\Http\Requests\User\Landlistings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LandListingCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'land_type' => 'required|in:زراعي,استثماري,سكني',
            'location' => 'required|string|max:255',
            'area' => 'required|numeric|min:5000',
            'description' => 'required|string',
            'deed_image' => 'required|string',
            'purpose' => 'required|in:بيع,استثمار',

            'price_per_meter' => 'required_if:purpose,بيع|nullable|numeric|min:0',
            'investment_start' => 'required_if:purpose,استثمار|nullable|date|before_or_equal:investment_end',
            'investment_end' => 'required_if:purpose,استثمار|nullable|date|after_or_equal:investment_start',
            'investment_estimated_value' => 'required_if:purpose,استثمار|nullable|numeric|min:0',

            'real_estate_announcement_no' => 'required|string|max:100',
            'no_dispute_confirmed' => 'required|boolean|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الإعلان مطلوب',
            'title.max' => 'عنوان الإعلان يجب ألا يتجاوز 150 حرفاً',
            'land_type.required' => 'نوع الأرض مطلوب',
            'land_type.in' => 'نوع الأرض يجب أن يكون زراعي، استثماري، أو سكني',
            'location.required' => 'الموقع مطلوب',
            'location.max' => 'الموقع يجب ألا يتجاوز 255 حرفاً',
            'area.required' => 'المساحة مطلوبة',
            'area.numeric' => 'المساحة يجب أن تكون رقماً',
            'area.min' => 'يجب أن تكون مساحة الأرض أكبر من أو تساوي 5000 متر مربع',
            'description.required' => 'الوصف مطلوب',
            'deed_image.required' => 'صورة الصك مطلوبة',
            'purpose.required' => 'الغرض من العرض مطلوب',
            'purpose.in' => 'الغرض يجب أن يكون بيع أو استثمار',

            'price_per_meter.required_if' => 'سعر المتر مطلوب في حالة البيع',
            'price_per_meter.numeric' => 'سعر المتر يجب أن يكون رقماً',
            'price_per_meter.min' => 'سعر المتر يجب أن يكون أكبر من الصفر',

            'investment_start.required_if' => 'تاريخ بدء الاستثمار مطلوب في حالة الاستثمار',
            'investment_start.date' => 'تاريخ بدء الاستثمار يجب أن يكون تاريخ صالح',
            'investment_start.before_or_equal' => 'تاريخ بدء الاستثمار يجب أن يكون قبل أو يساوي تاريخ نهاية الاستثمار',

            'investment_end.required_if' => 'تاريخ نهاية الاستثمار مطلوب في حالة الاستثمار',
            'investment_end.date' => 'تاريخ نهاية الاستثمار يجب أن يكون تاريخ صالح',
            'investment_end.after_or_equal' => 'تاريخ نهاية الاستثمار يجب أن يكون بعد أو يساوي تاريخ بدء الاستثمار',

            'investment_estimated_value.required_if' => 'القيمة التقريبية للاستثمار مطلوبة في حالة الاستثمار',
            'investment_estimated_value.numeric' => 'القيمة التقريبية للاستثمار يجب أن تكون رقماً',
            'investment_estimated_value.min' => 'القيمة التقريبية للاستثمار يجب أن تكون أكبر من الصفر',

            'real_estate_announcement_no.required' => 'رقم الإعلان العقاري مطلوب',
            'real_estate_announcement_no.max' => 'رقم الإعلان العقاري يجب ألا يتجاوز 100 حرف',

            'no_dispute_confirmed.required' => 'يجب تأكيد عدم وجود نزاع على الأرض',
            'no_dispute_confirmed.accepted' => 'يجب الموافقة على التعهد ',
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
