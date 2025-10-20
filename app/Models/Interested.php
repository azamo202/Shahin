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
        'land_id',
    ];

    // علاقة باليوزر (إذا مسجل)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة بالأرض
    public function land()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
