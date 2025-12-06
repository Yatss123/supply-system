<?php

namespace App\Notifications;

use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WeeklyStockUpdateReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Department $department
    ) {}

    public function via($notifiable): array
    {
        // Use database notifications for dean inbox; email can be added later if needed
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $month = now()->format('Y-m');
        return [
            'message' => sprintf(
                'Weekly reminder: Please update stocks for %s (%s).',
                $this->department->department_name,
                $month
            ),
            'url' => route('dean.allocations.show', [
                'department' => $this->department->id,
                'month' => $month,
            ]),
            'department_id' => $this->department->id,
            'month' => $month,
        ];
    }
}