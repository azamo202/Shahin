<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealEstateBroker extends Model
{
    protected $fillable = [
        'user_id',
        'national_id',
        'license_number',
        'license_file',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
