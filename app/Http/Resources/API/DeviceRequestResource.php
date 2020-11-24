<?php

namespace App\Http\Resources\API;

use App\Http\Resources\DevicesResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'device_id' => $this->device_id,
            'user' => UserResource::make($this->user),
            'device' => DevicesResource::make($this->device)
        ];
    }
}
