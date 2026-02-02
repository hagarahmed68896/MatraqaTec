<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'city_id',
        'type', // admin, individual, technician, maintenance_company (Partner), corporate_customer (Client Company)
        'phone',
        'avatar',
        'status',
        'blocked_at',
        'otp',
        'otp_expires_at',
        'wallet_balance',
        'fcm_token',
        'is_online',
        'address',
        'latitude',
        'longitude',
        'notification_enabled',
        'night_mode',
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'wallet_balance' => 'decimal:2',
            'is_online' => 'boolean',
            'notification_enabled' => 'boolean',
            'night_mode' => 'boolean',
        ];
    }

    /**
     * Set the phone attribute.
     * Normalizes the phone number by removing non-digits and leading country code/zero.
     *
     * @param string $value
     * @return void
     */
    public function setPhoneAttribute($value)
    {
        if ($value) {
            $phone = preg_replace('/[^0-9]/', '', $value);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $this->attributes['phone'] = $phone;
        }
    }

    public function individualCustomer()
    {
        return $this->hasOne(IndividualCustomer::class);
    }

    public function corporateCustomer()
    {
        return $this->hasOne(CorporateCustomer::class);
    }


    public function technician()
    {
        return $this->hasOne(Technician::class);
    }

    public function maintenanceCompany()
    {
        return $this->hasOne(MaintenanceCompany::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
                    ->wherePivot('model_type', self::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'model_has_permissions', 'model_id', 'permission_id')
                    ->wherePivot('model_type', self::class);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return !! $role->intersect($this->roles)->count();
    }

    public function hasPermission($permission)
    {
        return $this->permissions->contains('name', $permission) || 
               $this->roles->flatMap->permissions->contains('name', $permission);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function adminProfile()
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function searchHistories()
    {
        return $this->hasMany(SearchHistory::class);
    }
}
