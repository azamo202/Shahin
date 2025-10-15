<?php

namespace App\Http\Requests\User\Auctions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // حماية الكنترولر بالتوكن
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'intro_link' => 'nullable|url|max:255',
            'start_time' => 'sometimes|required|date_format:H:i',
            'auction_date' => 'sometimes|required|date',
            'address' => 'sometimes|required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'حقل العنوان مطلوب.',
            'title.string' => 'العنوان يجب أن يكون نصاً.',
            'title.max' => 'العنوان لا يمكن أن يزيد عن 255 حرفاً.',

            'description.string' => 'الوصف يجب أن يكون نصاً.',

            'intro_link.url' => 'رابط التعريف غير صالح.',
            'intro_link.max' => 'رابط التعريف لا يمكن أن يزيد عن 255 حرفاً.',

            'start_time.required' => 'حقل وقت البداية مطلوب.',
            'start_time.date_format' => 'وقت البداية يجب أن يكون بالصيغة HH:MM.',

            'auction_date.required' => 'حقل تاريخ المزاد مطلوب.',
            'auction_date.date' => 'تاريخ المزاد غير صالح.',

            'address.required' => 'حقل العنوان التفصيلي مطلوب.',
            'address.string' => 'العنوان التفصيلي يجب أن يكون نصاً.',
            'address.max' => 'العنوان التفصيلي لا يمكن أن يزيد عن 255 حرفاً.',

            'latitude.numeric' => 'خط العرض يجب أن يكون رقمياً.',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90.',

            'longitude.numeric' => 'خط الطول يجب أن يكون رقمياً.',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180.',
        ];
    }
}
