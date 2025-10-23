<?php

namespace App\Models;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
  use App\Notifications\ResetPasswordCustom; // Notification مخصص

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;



public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordCustom($token));
}



    // ✅ Constants للحالات لتجنب الأخطاء المطبعية
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'user_type_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    // علاقة نوع المستخدم

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }
    // علاقة المستخدم بالأراضي
    public function property()
    {
        return $this->hasMany(property::class);
    }

    // ✅ دوال التحقق من الحالة
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
    public function landOwner()
    {
        return $this->hasOne(LandOwner::class);
    }
    public function legalAgent()
    {
        return $this->hasOne(LegalAgent::class);
    }
    public function businessEntity()
    {
        return $this->hasOne(BusinessEntity::class);
    }
    public function realEstateBroker()
    {
        return $this->hasOne(RealEstateBroker::class);
    }
    public function auctionCompany()
    {
        return $this->hasOne(AuctionCompany::class);
    }
    // في موديل User
    public function isInactive(): bool
    {
        return $this->isPending(); // أي حساب قيد المراجعة يعتبر غير نشط
    }
    public function auctions()
    {
        return $this->hasMany(Auction::class, 'user_id');
    }
}
