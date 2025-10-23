<?php

namespace App\Models;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordCustom;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordCustom($token));
    }

    // ✅ Constants للحالات
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'user_type_id',
        'status',
        'last_login_at', // إضافة هذا الحقل إذا لم يكن موجوداً
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
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

    // علاقة المستخدم بالأراضي - تصحيح اسم الكلاس
    public function properties()
    {
        return $this->hasMany(Property::class);
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

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE || $this->isPending();
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    // العلاقات مع أنواع المستخدمين المختلفة
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

    // العلاقات الجديدة المطلوبة للكنترولر
    public function auctions()
    {
        return $this->hasMany(Auction::class, 'user_id');
    }

   

    // دالة scope للبحث
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%')
              ->orWhere('phone', 'like', '%' . $search . '%');
        });
    }

    // دالة scope للتصفية حسب النوع
    public function scopeOfType($query, $userTypeId)
    {
        return $query->where('user_type_id', $userTypeId);
    }

    // دالة scope للتصفية حسب الحالة
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // دالة للحصول على نوع المستخدم كـ string
    public function getUserTypeName(): string
    {
        return $this->userType->type_name ?? 'غير محدد';
    }

    // دالة للتحقق إذا كان المستخدم لديه ملف تعريف مكتمل
    public function hasCompleteProfile(): bool
    {
        if ($this->userType) {
            switch ($this->userType->type_name) {
                case 'land_owner':
                    return !is_null($this->landOwner);
                case 'legal_agent':
                    return !is_null($this->legalAgent);
                case 'business_entity':
                    return !is_null($this->businessEntity);
                case 'real_estate_broker':
                    return !is_null($this->realEstateBroker);
                case 'auction_company':
                    return !is_null($this->auctionCompany);
                default:
                    return true;
            }
        }
        return false;
    }

    // دالة للحصول على تفاصيل الملف الشخصي
    public function getProfileDetails(): ?array
    {
        if ($this->landOwner) {
            return [
                'type' => 'land_owner',
                'company_name' => $this->landOwner->company_name,
                'license_number' => $this->landOwner->license_number,
            ];
        }

        if ($this->legalAgent) {
            return [
                'type' => 'legal_agent',
                'bar_association_number' => $this->legalAgent->bar_association_number,
                'practice_area' => $this->legalAgent->practice_area,
            ];
        }

        if ($this->businessEntity) {
            return [
                'type' => 'business_entity',
                'entity_name' => $this->businessEntity->entity_name,
                'commercial_registration' => $this->businessEntity->commercial_registration,
            ];
        }

        if ($this->realEstateBroker) {
            return [
                'type' => 'real_estate_broker',
                'broker_license' => $this->realEstateBroker->broker_license,
                'experience_years' => $this->realEstateBroker->experience_years,
            ];
        }

        if ($this->auctionCompany) {
            return [
                'type' => 'auction_company',
                'company_name' => $this->auctionCompany->company_name,
                'auction_license' => $this->auctionCompany->auction_license,
            ];
        }

        return null;
    }
}