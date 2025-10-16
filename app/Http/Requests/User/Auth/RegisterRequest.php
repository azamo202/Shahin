<?php
namespace App\Http\Requests\User\Auth;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // قواعد أساسية مشتركة
        $rules = [
            'full_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_type_id' => 'required|integer|exists:user_types,id',
        ];

        // قواعد عامة للـ national_id: مطلوب لأنك قلت إنه موجود لجميع الأنواع 2..6
        // سنضع قواعد أساسية هنا ثم نضيف تحقق unique ديناميكي عبر Closure لاحقًا.
        $rules['national_id'] = [
            'required_if:user_type_id,2,3,4,5,6',
            'string',
            'max:50',
            // Closure للتحقق من الـ unique في الجدول الصحيح حسب user_type_id
            function ($attribute, $value, $fail) {
                $type = $this->input('user_type_id');

                if (!$type) {
                    return; // لو ما فيه type محدد، دعه يفشل على قواعد أخرى
                }

                $map = [
                    2 => 'land_owners',
                    3 => 'legal_agents',
                    4 => 'business_entities',
                    5 => 'real_estate_brokers',
                    6 => 'auction_companies',
                ];

                if (!array_key_exists($type, $map)) {
                    return;
                }

                $table = $map[$type];

                // نتحقق إن القيمة موجودة مسبقاً في جدول النوع المحدد
                $exists = DB::table($table)->where('national_id', $value)->exists();

                if ($exists) {
                    $niceTable = [
                        'land_owners' => 'مالك أرض',
                        'legal_agents' => 'وكيل شرعي',
                        'business_entities' => 'منشأة تجارية',
                        'real_estate_brokers' => 'وسيط عقاري',
                        'auction_companies' => 'شركة مزاد',
                    ][$table] ?? $table;

                    $fail("رقم الهوية مستخدم من قبل في سجلات {$niceTable}.");
                }
            },
        ];

        // الآن قواعد خاصة لكل نوع موزّعة بشكل واضح (B - مفصّل)
        switch ((int) $this->input('user_type_id')) {
            case 2: // land_owners
                // لا حاجة لحقل إضافي غير national_id
                break;

            case 3: // legal_agents
                $rules['agency_number'] = [
                    'required_if:user_type_id,3',
                    'string',
                    'max:100',
                    Rule::unique('legal_agents', 'agency_number'),
                ];
                break;

            case 4: // business_entities
                $rules = array_merge($rules, [
                    'business_name' => 'required_if:user_type_id,4|string|max:150',
                    'commercial_register' => [
                        'required_if:user_type_id,4',
                        'string',
                        'max:100',
                        Rule::unique('business_entities', 'commercial_register'),
                    ],
                    'commercial_register_file' => 'required_if:user_type_id,4|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ]);
                break;

            case 5: // real_estate_brokers
                $rules = array_merge($rules, [
                    'license_number' => [
                        'required_if:user_type_id,5',
                        'string',
                        'max:100',
                        Rule::unique('real_estate_brokers', 'license_number'),
                    ],
                    'license_file' => 'required_if:user_type_id,5|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ]);
                break;

            case 6: // auction_companies
                $rules = array_merge($rules, [
                    'auction_name' => 'required_if:user_type_id,6|string|max:150',
                    // commercial_register_file موجود في مخططك كـ commercial_register_file
                    'commercial_register_file' => 'required_if:user_type_id,6|file|mimes:jpg,jpeg,png,pdf|max:5120',
                    'license_number' => [
                        'required_if:user_type_id,6',
                        'string',
                        'max:100',
                        Rule::unique('auction_companies', 'license_number'),
                    ],
                    'license_file' => 'required_if:user_type_id,6|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ]);
                break;

            default:
                // لو نوع غير معروف، لا تضيف قواعد إضافية
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'full_name.max' => 'الاسم طويل جداً',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',

            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور قصيرة جداً',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',

            'phone.max' => 'رقم الهاتف طويل جداً',

            'user_type_id.required' => 'نوع المستخدم مطلوب',
            'user_type_id.exists' => 'نوع المستخدم غير صحيح',

            'national_id.required_if' => 'رقم الهوية مطلوب لهذا النوع من المستخدمين',
            'national_id.max' => 'رقم الهوية طويل جداً',

            'agency_number.required_if' => 'رقم الوكالة مطلوب',
            'agency_number.max' => 'رقم الوكالة طويل جداً',
            'agency_number.unique' => 'رقم الوكالة مستخدم من قبل',

            'business_name.required_if' => 'اسم المنشأة/الشركة مطلوب',
            'business_name.max' => 'اسم المنشأة طويل جداً',

            'commercial_register.required_if' => 'رقم السجل التجاري مطلوب',
            'commercial_register.unique' => 'رقم السجل التجاري مستخدم من قبل',
            'commercial_register_file.required_if' => 'ملف السجل التجاري مطلوب',
            'commercial_register_file.file' => 'ملف السجل التجاري غير صالح',
            'commercial_register_file.mimes' => 'الصيغ المسموح بها: jpg, jpeg, png, pdf',
            'commercial_register_file.max' => 'حجم الملف أكبر من المسموح (5MB)',

            'license_number.required_if' => 'رقم الترخيص مطلوب',
            'license_number.unique' => 'رقم الترخيص مستخدم من قبل',
            'license_file.required_if' => 'ملف الترخيص مطلوب',
            'license_file.file' => 'ملف الترخيص غير صالح',
            'license_file.mimes' => 'الصيغ المسموح بها: jpg, jpeg, png, pdf',
            'license_file.max' => 'حجم الملف أكبر من المسموح (5MB)',

            // fallback
            'required' => 'هذا الحقل مطلوب',
        ];
    }
}
