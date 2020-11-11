<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'users' => UserResource::make($this->user_id),
            'devices' => DevicesResource::make($this->device_id),
            'log_detail' => $this->log_detail,
        ];
    }
}
