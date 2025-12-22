<?php

namespace App\Events;

use App\Models\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Job $job;
    public string $updateType;
    public ?string $updatedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Job $job, string $updateType = 'status', ?string $updatedBy = null)
    {
        $this->job = $job;
        $this->updateType = $updateType;
        $this->updatedBy = $updatedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('jobs'),
            new PrivateChannel('job.' . $this->job->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'job.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->job->id,
            'job_number' => $this->job->job_number,
            'plate_number' => $this->job->plate_number,
            'status' => $this->job->status,
            'work_status' => $this->job->work_status,
            'update_type' => $this->updateType,
            'updated_by' => $this->updatedBy,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
