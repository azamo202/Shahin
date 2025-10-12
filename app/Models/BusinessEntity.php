<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEntity extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'commercial_register',
        'national_id',
        'commercial_file',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
