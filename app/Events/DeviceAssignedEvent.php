<?php

namespace App\Events;

use App\Http\Resources\DevicesResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Log as FacadesLog;

class DeviceAssignedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \App\Models\Device */
    private $device;
    /** @var \App\User */
    private $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Device $device, User $user)
    {
        //
        $this->device = $device;
        $this->user   =  $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['DevicesAvailabilty'];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'device' => DevicesResource::make($this->device),
        ];
    }
}