<?php

namespace App\Events;

use App\Http\Requests\DeviceRequest as RequestsDeviceRequest;
use App\Http\Resources\DevicesResource;
use App\Models\DeviceLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
// use App\Models\Device;
use App\Models\DeviceRequest;
use Illuminate\Support\Facades\Log as FacadesLog;

class DeviceAssignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \App\Models\DeviceRequest */
    private $device_request;
    private $log;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DeviceRequest $device_request, DeviceLog $log)
    {
        //
        $this->device_request = $device_request;
        $this->log            = $log;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['DevicesAssignment'];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'request' => ['id'=> $this->device_request->id, 'request_status' => $this->device_request->request_status],
            'device'  => ['id' => $this->device_request->device->id, 'status' => $this->device_request->device->status],
            'log'     => $this->log
        ];
    }
}
