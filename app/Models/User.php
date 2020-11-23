<?php

namespace App\Models;

use App\Models\DeviceLog;
use App\Models\DeviceRequest;
use App\Traits\GetAuthUserTrait;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * App\User
 *
 * @property int $id
 * @property string $locale
 * @property string $username
 * @property string $name
 * @property string|null $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property Carbon|null $date_of_birth
 * @property Carbon|null $email_verified_at
 * @property string|null $verify_email_token
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DeviceLog[] $assigned_devices
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DeviceRequest[] $device_requests
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @mixin \Eloquent
 */

class User extends Authenticatable
{
    use HasRolesAndAbilities;
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, Notifiable, GetAuthUserTrait, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'reset_password_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the assigned devices to suer
     */
    public function assigned_devices()
    {
        return $this->hasMany(DeviceLog::class);
    }

    /**
     * Get the requested devices
     */
    public function device_requests()
    {
        return $this->hasMany(DeviceRequest::class);
    }

    public function setPasswordAttribute($pass) {

        if (Hash::needsRehash($pass)) {
            $this->attributes['password'] = Hash::make($pass);
        } else {
            $this->attributes['password'] = $pass;
        }
    }
}
