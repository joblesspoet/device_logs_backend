<?php

namespace App\Http\Resources;

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceLogsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = User::find($this->user_id);
        $device = Device::find($this->device_id);
        return [
            'id' => $this->id,
            'users' => UserResource::make($user),
            'devices' => DevicesResource::make($device),
            'log_detail' => $this->log_detail,
        ];
    }
}
