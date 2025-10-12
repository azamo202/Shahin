<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionCompany extends Model
{
    protected $fillable = [
        'user_id',
        'commercial_register',
        'auction_name',
        'national_id',
        'commercial_file',
        'license_number',
        'license_file',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
