<?php

namespace App\Http\Requests\User\Landlistings;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // إذا كان كل المستخدمين مسموحين بإضافة العقار
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // بيانات أساسية
            'announcement_number' => 'required|string|max:50',
            'region' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'land_type' => 'required|in:سكني,تجاري,زراعي',
            'purpose' => 'required|in:بيع,استثمار',

            // الموقع
            'geo_location_text' => 'required|string|max:255',
            'geo_location_map' => 'nullable|string|max:255',

            // تفاصيل العقار
            'total_area' => 'required|numeric|min:0',
            'length_north' => 'required|numeric|min:0',
            'length_south' => 'required|numeric|min:0',
            'length_east' => 'required|numeric|min:0',
            'length_west' => 'required|numeric|min:0',
            'description' => 'required|string',
            'deed_number' => 'required|string|max:50',

            // الصور الإضافية
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',

            // الأسعار والاستثمار
            'price_per_sqm' => 'required_if:purpose,بيع|nullable|numeric|min:0',
            'investment_duration' => 'required_if:purpose,استثمار|nullable|integer|min:1',
            'estimated_investment_value' => 'required_if:purpose,استثمار|nullable|numeric|min:0',

            // الوكالة والتعهد
            'agency_number' => 'required_if:user_type,3|nullable|string|max:50',
        ];

        // ✅ إذا كان إنشاء (POST) → صورة الغلاف مطلوبة + التعهد مطلوب
        if ($this->isMethod('post')) {
            $rules['cover_image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120';
            $rules['legal_declaration'] = 'accepted';
        }

        // ✅ إذا كان تعديل (PUT/PATCH) → الغلاف اختياري + التعهد اختياري
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['cover_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
            $rules['legal_declaration'] = 'sometimes|accepted';
        }

        return $rules;
    }
}
