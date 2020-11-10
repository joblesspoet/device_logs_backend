<?php

namespace Modules\ApplicationAuth\Entities;


use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    // Locales constants
    const LOCALE_EN = "en";
    const LOCALE_NL = "nl";
    const LOCALE_BE = "be";
    const LOCALE_SP = "sp";

    const AVAILABLE_LOCALES = [
        self::LOCALE_EN,
        self::LOCALE_NL,
        self::LOCALE_BE,
        self::LOCALE_SP,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'locale',
        'password',
        'signature',
        'image_url',
        'email_verified_at',
        'verify_email_token',
        'reset_password_token',
        'reset_password_token_expires',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'reset_password_token_expires' => 'datetime',
    ];

    /**
     * @inheritDoc
     */
    public function hasVerifiedEmail()
    {
        return !$this->email || !!$this->email_verified_at;
    }

    /**
     * @inheritDoc
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


}
