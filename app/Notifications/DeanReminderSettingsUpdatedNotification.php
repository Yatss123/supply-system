<?php

namespace App\Notifications;

use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeanReminderSettingsUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Department $department,
        protected ?int $day
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $dayName = $this->day ? self::dayName($this->day) : null;
        $message = $this->day
            ? sprintf('Reminder updated: You will receive weekly stock update notifications every %s for %s.', $dayName, $this->department->department_name)
            : sprintf('Reminders disabled: You will no longer receive weekly stock update notifications for %s.', $this->department->department_name);

        return [
            'message' => $message,
            'url' => route('dean.allocations.show', [
                'department' => $this->department->id,
                'month' => now()->format('Y-m'),
            ]),
            'department_id' => $this->department->id,
            'reminder_day' => $this->day,
            'reminder_day_name' => $dayName,
        ];
    }

    public static function dayName(int $day): string
    {
        // ISO-8601 1..7 (Monday..Sunday)
        $map = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        return $map[$day] ?? 'Unknown';
    }
}