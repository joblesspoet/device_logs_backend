<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DeviceRequest extends Model
{
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
}
