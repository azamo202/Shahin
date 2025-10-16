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

            // تحديث غلاف المزاد
            'cover_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // تحديث الصور
            'images' => 'sometimes|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // تحديث الفيديوهات
            'videos' => 'sometimes|array',
            'videos.*' => 'mimetypes:video/mp4,video/mpeg,video/quicktime|max:10240',
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

            'cover_image.image' => 'الغلاف يجب أن يكون صورة.',
            'cover_image.mimes' => 'الغلاف يجب أن يكون من نوع jpeg, png, jpg, gif, webp.',
            'cover_image.max' => 'حجم الغلاف لا يمكن أن يزيد عن 2MB.',

            'images.array' => 'صور المزاد يجب أن تكون مصفوفة.',
            'images.min' => 'يجب رفع صورة واحدة على الأقل.',
            'images.*.image' => 'كل عنصر من الصور يجب أن يكون صورة.',
            'images.*.mimes' => 'نوع الصور يجب أن يكون jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'حجم كل صورة لا يمكن أن يزيد عن 2MB.',

            'videos.array' => 'فيديوهات المزاد يجب أن تكون مصفوفة.',
            'videos.*.mimetypes' => 'نوع الفيديو يجب أن يكون mp4, mpeg, quicktime.',
            'videos.*.max' => 'حجم كل فيديو لا يمكن أن يزيد عن 10MB.',
        ];
    }
}
