<?php

namespace App\Notifications;

use App\Models\InterDepartmentLoanRequest;
use App\Models\InterDepartmentReturnRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterDepartmentReturnNotification extends Notification
{
    use Queueable;

    protected InterDepartmentLoanRequest $loanRequest;
    protected InterDepartmentReturnRecord $returnRecord;

    /**
     * Create a new notification instance.
     */
    public function __construct(InterDepartmentLoanRequest $loanRequest, InterDepartmentReturnRecord $returnRecord)
    {
        $this->loanRequest = $loanRequest;
        $this->returnRecord = $returnRecord;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/inter-department-loans/' . $this->loanRequest->id);

        $mail = (new MailMessage)
            ->subject('Inter-Department Return Initiated: Details for Review')
            ->greeting('Return Details Submitted')
            ->line('A return has been initiated for an inter-department borrowed item. Please review the details below and take action if needed:')
            ->line('Supply: ' . $this->loanRequest->issuedItem->supply->name)
            ->line('From Department: ' . $this->loanRequest->lendingDepartment->department_name)
            ->line('To Department: ' . $this->loanRequest->borrowingDepartment->department_name)
            ->line('Requested By: ' . $this->loanRequest->requestedBy->name)
            ->line('Return Initiated By: ' . optional($this->returnRecord->initiatedBy)->name)
            ->line('Missing Items: ' . ($this->returnRecord->missing_count ?? 0))
            ->line('Damaged Items: ' . ($this->returnRecord->damaged_count ?? 0))
            ->line('Damage Severity: ' . ($this->returnRecord->damage_severity ? ucwords(str_replace('_', ' ', $this->returnRecord->damage_severity)) : 'N/A'));

        if ($this->returnRecord->notes) {
            $mail->line('Notes: ' . $this->returnRecord->notes);
        }
        if ($this->returnRecord->photo_path) {
            $mail->line('A photo was attached to the return.');
        }

        return $mail->action('View Return', $url)
            ->line('Thank you for using our supply management system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'loan_request_id' => $this->loanRequest->id,
            'return_record_id' => $this->returnRecord->id,
            'missing_count' => $this->returnRecord->missing_count,
            'damaged_count' => $this->returnRecord->damaged_count,
            'damage_severity' => $this->returnRecord->damage_severity,
            'notes' => $this->returnRecord->notes,
        ];
    }
}