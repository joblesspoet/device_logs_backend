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

    public function getStatusAttribute()
    {
        return $this->device->status;
    }
}
