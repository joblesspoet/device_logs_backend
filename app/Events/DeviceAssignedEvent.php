<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Device;
use App\Models\User;

class DeviceAssignedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var \App\Models\Device */
    protected $device;
    /** @var \App\User */
    protected $user;

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
        return new PrivateChannel('User.'.$this->user->id);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'device' => $this->device,
            'user'  => $this->user,
        ];
    }
}
