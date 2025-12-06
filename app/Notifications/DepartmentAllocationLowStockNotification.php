<?php

namespace App\Notifications;

use App\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DepartmentAllocationLowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Department $department,
        protected string $month,
        protected int $lowCount
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => sprintf(
                'Low stock detected: %d consumable items for %s (%s).',
                $this->lowCount,
                $this->department->department_name,
                $this->month
            ),
            'url' => route('dean.allocations.show', [
                'department' => $this->department->id,
                'month' => $this->month,
            ]),
            'department_id' => $this->department->id,
            'month' => $this->month,
            'low_count' => $this->lowCount,
        ];
    }
}