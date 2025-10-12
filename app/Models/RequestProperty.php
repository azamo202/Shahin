<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestProperty extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'user_id', 'title', 'city', 'area', 'request_type',
        'land_type', 'min_area', 'max_area', 'price'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
