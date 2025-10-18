<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedClient extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'logo', 'website'];
}
