<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DevicesResource extends JsonResource
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
            'device_name' => $this->device_name,
            'device_model' => $this->device_model,
            'device_version' => $this->device_version,
            'device_picture' => $this->device_picture,
            'status' => $this->status
        ];
    }
}

