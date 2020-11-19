<?php

namespace App\Models;

use Str;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Device extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    /**
     * The attributes that are const.
     *
     * @var const
     */
    const INUSE = "INUSE";
    const AVAILABLE = "AVAILABLE";
    const STATUS = [
        self::AVAILABLE,
        self::INUSE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'device_name',
        'device_model',
        'device_version',
        'device_picture',
        'status'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Get the device owned by user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the requested devices
     */
    public function request_devices()
    {
        return $this->hasMany(DeviceRequest::class);
    }
    
    /**
     * Get the device logs
     */
    public function device_logs()
    {
        return $this->hasMany(DeviceLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    /**
     * setLogoAttribute function
     *
     * @param [type] $value
     * @return void
     */
    public function setDevicePictureAttribute($value)
    {
        $disk = Storage::disk("public");

        if (
            $value instanceof \SplFileInfo ||
            (is_string($value) && preg_match('#^(https?://|data:image/)#', $value))
        ) {

            if (
                (is_string($value) &&
                    preg_match('#http?://#', $value))
            ) {

                return;
            }

            $image = Image::make($value);

            do {
                $filename = Str::random(40);
            } while (Storage::exists("Devices/{$filename}.jpg"));

            $disk->put("Devices/{$filename}.jpg", $image->stream('jpg'));
            $this->attributes['device_picture'] = "Devices/{$filename}.jpg";
        } else if (is_string($value) && $disk->exists("Devices/{$value}")) {
            $this->attributes['device_picture'] = $value;
        }
    }
}
