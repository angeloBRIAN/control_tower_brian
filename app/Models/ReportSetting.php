<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'name',
        'description',
        'is_enabled',
        'schedule',
        'schedule_time',
        'schedule_day',
        'recipients',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'recipients' => 'array',
        ];
    }

    /**
     * Get recipients as array of emails.
     */
    public function getRecipientsListAttribute(): array
    {
        return $this->recipients ?? [];
    }

    /**
     * Add a recipient email.
     */
    public function addRecipient(string $email): void
    {
        $recipients = $this->recipients ?? [];
        if (!in_array($email, $recipients)) {
            $recipients[] = $email;
            $this->update(['recipients' => $recipients]);
        }
    }

    /**
     * Remove a recipient email.
     */
    public function removeRecipient(string $email): void
    {
        $recipients = $this->recipients ?? [];
        $recipients = array_filter($recipients, fn($e) => $e !== $email);
        $this->update(['recipients' => array_values($recipients)]);
    }

    /**
     * Get the schedule description.
     */
    public function getScheduleDescriptionAttribute(): string
    {
        $day = match($this->schedule_day) {
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday',
            '7' => 'Sunday',
            default => $this->schedule_day,
        };

        return match($this->schedule) {
            'daily' => "Daily at {$this->schedule_time}",
            'weekly' => "Every {$day} at {$this->schedule_time}",
            'monthly' => "Monthly on day {$this->schedule_day} at {$this->schedule_time}",
            default => $this->schedule,
        };
    }
}
