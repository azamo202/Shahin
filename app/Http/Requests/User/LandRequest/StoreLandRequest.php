<?php

namespace App\Http\Requests\User\LandRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLandRequest extends FormRequest
{
    /**
     * تحديد صلاحية المستخدم لإرسال هذا الطلب.
     * هنا يمكن تعديل الشرط لاحقًا في حال أردت التحقق من صلاحيات المستخدم.
     */
    public function authorize(): bool
    {
        return true; // السماح لجميع المستخدمين المسجلين بإرسال الطلب
    }

    /**
     * القواعد الخاصة بالتحقق من البيانات (Validation Rules)
     */
    public function rules(): array
    {
        return [
            'region' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'purpose' => ['required', Rule::in(['sale', 'investment'])],
            'type' => ['required', Rule::in(['residential', 'commercial', 'agricultural'])],
            'area' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * الرسائل المخصصة للأخطاء (Messages)
     * تمت كتابتها بالعربية لواجهة عربية احترافية.
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
     * أسماء الحقول بالعربية لتظهر بشكل جميل في رسائل الخطأ.
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
