<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandOwner extends Model
{
    protected $fillable = ['user_id', 'national_id'];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
