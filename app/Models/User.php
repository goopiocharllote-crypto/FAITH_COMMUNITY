<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public const ROLE_PARISHIONER = 'parishioner';

    public const ROLE_ADMINISTRATOR = 'administrator';

    public const DEACTIVATED_MESSAGE = 'This account has been deactivated. Please contact the parish office if you think this is a mistake.';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
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
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Philippine mobile number in local format for SMS (stored as 9XXXXXXXXX).
     */
    public function routeNotificationForSms(): ?string
    {
        return $this->mobile && ! $this->trashed() ? '0'.$this->mobile : null;
    }

    /** Deleted accounts keep their name on parish records but get no more email. */
    public function routeNotificationForMail(): ?string
    {
        return $this->trashed() ? null : $this->email;
    }

    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    public function massIntentions(): HasMany
    {
        return $this->hasMany(MassIntention::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function prayerRequests(): HasMany
    {
        return $this->hasMany(PrayerRequest::class);
    }
}
