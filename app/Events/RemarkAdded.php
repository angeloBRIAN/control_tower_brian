<?php

namespace App\Events;

use App\Models\Job;
use App\Models\Remark;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemarkAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Job $job;
    public Remark $remark;

    /**
     * Create a new event instance.
     */
    public function __construct(Job $job, Remark $remark)
    {
        $this->job = $job;
        $this->remark = $remark;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('job.' . $this->job->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'remark.added';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'job_id' => $this->job->id,
            'remark' => [
                'id' => $this->remark->id,
                'text' => $this->remark->remark_text,
                'commenter_name' => $this->remark->commenter_name,
                'initials' => $this->remark->commenter_initials,
                'time_ago' => 'just now',
                'created_at' => $this->remark->created_at->toIso8601String(),
            ],
        ];
    }
}
