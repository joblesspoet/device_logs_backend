<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
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
    protected $casts = [

    ];

    
}
