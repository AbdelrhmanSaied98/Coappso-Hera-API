<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Messaging implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $content;
    public $content_type;
    public $customer_id;
    public $beauty_center_id;
    public $sender_type;
    public $created_at;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        if($message->content_type == 'text')
        {
            $this->id = $message->id;
            $this->content = $message->content;
            $this->content_type = $message->content_type;
            $this->customer_id = $message->customer_id;
            $this->beauty_center_id = $message->beauty_center_id;
            $this->sender_type = $message->sender_type;
            $this->created_at = $message->created_at;
        }else
        {
            $message->content = asset('/assets/messages/' . $message->content );
            $this->id = $message->id;
            $this->content = $message->content;
            $this->content_type = $message->content_type;
            $this->customer_id = $message->customer_id;
            $this->beauty_center_id = $message->beauty_center_id;
            $this->sender_type = $message->sender_type;
            $this->created_at = $message->created_at;
        }

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel($this->beauty_center_id.$this->customer_id);
    }
}
