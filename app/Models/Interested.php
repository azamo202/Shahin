<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interested extends Model
{
    use HasFactory;

    protected $table = 'interested';

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'message',
        'user_id',
        'property_id',
        'status',
        'admin_notes',
    ];

    // علاقة باليوزر (إذا مسجل)
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'full_name' => 'مستخدم غير مسجل'
        ]);
    }
    // علاقة بالأرض
    public function land()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
    public function property()
    {
        return $this->belongsTo(Property::class)->withDefault([
            'title' => 'عقار محذوف',
            'reference_number' => 'N/A'
        ]);
    }

    const STATUS_PENDING = 'قيد المراجعة';
    const STATUS_REVIEWED = 'تمت المراجعة';
    const STATUS_CONTACTED = 'تم التواصل';
    const STATUS_FAILD = 'ملغي';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_REVIEWED,
            self::STATUS_CONTACTED,
            self::STATUS_FAILD,
        ];
    }
}
