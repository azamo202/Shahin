<?php

namespace App\Http\Requests\User\LandRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLandRequest extends FormRequest
{
    /**
     * السماح بتنفيذ الطلب
     */
    public function authorize(): bool
    {
        // بإمكانك لاحقًا وضع شرط للتأكد من أن المستخدم يملك هذا الطلب
        return true;
    }

    /**
     * القواعد الخاصة بالتحقق من صحة البيانات
     */
    public function rules(): array
    {
        return [
            'region' => ['sometimes', 'required', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'purpose' => ['sometimes', 'required', Rule::in(['sale', 'investment'])],
            'type' => ['sometimes', 'required', Rule::in(['residential', 'commercial', 'agricultural'])],
            'area' => ['sometimes', 'required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * الرسائل المخصصة للأخطاء
     */
    public function messages(): array
    {
        return [
            'region.required' => '🟠 يرجى إدخال اسم المنطقة.',
            'region.string' => '❌ يجب أن تكون المنطقة نصية فقط.',
            'region.max' => '❌ الحد الأقصى لطول اسم المنطقة هو 255 حرفًا.',

            'city.required' => '🟠 يرجى إدخال اسم المدينة.',
            'city.string' => '❌ يجب أن تكون المدينة نصية فقط.',
            'city.max' => '❌ الحد الأقصى لطول اسم المدينة هو 255 حرفًا.',

            'purpose.required' => '🟠 يرجى تحديد الغرض من الطلب (بيع أو استثمار).',
            'purpose.in' => '❌ الغرض يجب أن يكون إما "sale" أو "investment".',

            'type.required' => '🟠 يرجى تحديد نوع الأرض (سكنية، تجارية، زراعية).',
            'type.in' => '❌ النوع يجب أن يكون أحد القيم التالية: residential, commercial, agricultural.',

            'area.required' => '🟠 يرجى إدخال مساحة الأرض.',
            'area.numeric' => '❌ يجب أن تكون المساحة رقمًا صحيحًا.',
            'area.min' => '❌ الحد الأدنى للمساحة هو 1 متر مربع.',

            'description.string' => '❌ الوصف يجب أن يكون نصيًا فقط.',
            'description.max' => '❌ الحد الأقصى لطول الوصف هو 500 حرف.',
        ];
    }

    /**
     * أسماء الحقول بالعربية
     */
    public function attributes(): array
    {
        return [
            'region' => 'المنطقة',
            'city' => 'المدينة',
            'purpose' => 'الغرض',
            'type' => 'النوع',
            'area' => 'المساحة',
            'description' => 'الوصف',
        ];
    }
}
