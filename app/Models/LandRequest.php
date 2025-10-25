<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'region',
        'city',
        'purpose',
        'type',
        'area',
        'description',
        'status'
    ];

    protected $casts = [
        'area' => 'float',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
