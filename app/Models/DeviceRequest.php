<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceRequest extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'device_id',
        'request_detail',
        'request_status'
    ];

    const STATUS_PENDING        = "PENDING";
    const STATUS_PLASE_COLLECT  = "PLEASE_COLLECT";
    const STATUS_APPROVED       = "APPROVED";

    const REQUEST_STATUS = [
        self::STATUS_PENDING => self::STATUS_PENDING,
        self::STATUS_PLASE_COLLECT => self::STATUS_PLASE_COLLECT,
        self::STATUS_APPROVED => self::STATUS_APPROVED,
    ];

    /**
     * Get the device request owned by user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the device that has request
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

     /**
     * Get the devices logs
     */
    public function device_logs()
    {
        return $this->hasMany(DeviceLog::class);
    }

    // public function getStatusAttribute()
    // {
    //     return $this->device->status;
    // }
}
