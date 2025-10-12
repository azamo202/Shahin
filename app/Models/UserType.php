<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    protected $fillable = ['type_name'];

    public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
