<?php

namespace App\Notifications;

use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DepartmentAllocationReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Department $department,
        protected string $month,
        protected int $readyCount
    ) {}

    public function via($notifiable): array
    {
        // Use database notifications for dean inbox; email can be added later if needed
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => sprintf(
                '%d item(s) staged as Ready to Pick Up for %s (%s).',
                $this->readyCount,
                $this->department->department_name,
                $this->month
            ),
            'url' => route('dean.allocations.show', [
                'department' => $this->department->id,
                'month' => $this->month,
            ]),
            'department_id' => $this->department->id,
            'month' => $this->month,
            'ready_count' => $this->readyCount,
        ];
    }
}