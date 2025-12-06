<?php

namespace App\Notifications;

use App\Models\InterDepartmentLoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterDepartmentLoanNotification extends Notification
{
    use Queueable;

    protected $loanRequest;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(InterDepartmentLoanRequest $loanRequest, $type = 'created')
    {
        $this->loanRequest = $loanRequest;
        $this->type = $type;
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
        
        $subject = $this->getSubject();
        $greeting = $this->getGreeting();
        $line1 = $this->getMainMessage();
        
        $mailMessage = (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($line1)
                    ->line('Supply: ' . $this->loanRequest->issuedItem->supply->name)
                    ->line('Quantity Requested: ' . $this->loanRequest->quantity_requested)
                    ->line('From Department: ' . $this->loanRequest->lendingDepartment->department_name)
                    ->line('To Department: ' . $this->loanRequest->borrowingDepartment->department_name)
                    ->line('Requested By: ' . $this->loanRequest->requestedBy->name)
                    ->line('Purpose: ' . $this->loanRequest->purpose)
                    ->line('Expected Return Date: ' . $this->loanRequest->expected_return_date->format('F d, Y'))
                    ->action('View Request', $url);

        if ($this->type === 'auto_approved') {
            $mailMessage->line('This request was automatically approved because it was created by a department dean.');
            if ($this->loanRequest->dean_approval_notes) {
                $mailMessage->line('Notes: ' . $this->loanRequest->dean_approval_notes);
            }
        }

        return $mailMessage->line('Thank you for using our supply management system!');
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
            'type' => $this->type,
            'supply_name' => $this->loanRequest->issuedItem->supply->name,
            'quantity_requested' => $this->loanRequest->quantity_requested,
            'lending_department' => $this->loanRequest->lendingDepartment->department_name,
            'borrowing_department' => $this->loanRequest->borrowingDepartment->department_name,
            'requested_by' => $this->loanRequest->requestedBy->name,
            'status' => $this->loanRequest->status,
        ];
    }

    /**
     * Get the subject line for the notification
     */
    private function getSubject(): string
    {
        switch ($this->type) {
            case 'created':
                return 'New Inter-Department Loan Request';
            case 'auto_approved':
                return 'Inter-Department Loan Request Auto-Approved';
            case 'approved':
                return 'Inter-Department Loan Request Approved';
            case 'return_initiated':
                return 'Inter-Department Loan Return Initiated';
            case 'declined':
                return 'Inter-Department Loan Request Declined';
            default:
                return 'Inter-Department Loan Request Update';
        }
    }

    /**
     * Get the greeting for the notification
     */
    private function getGreeting(): string
    {
        switch ($this->type) {
            case 'created':
                return 'New Request Submitted';
            case 'auto_approved':
                return 'Request Auto-Approved';
            case 'approved':
                return 'Request Approved';
            case 'return_initiated':
                return 'Return Initiated';
            case 'declined':
                return 'Request Declined';
            default:
                return 'Request Update';
        }
    }

    /**
     * Get the main message for the notification
     */
    private function getMainMessage(): string
    {
        switch ($this->type) {
            case 'created':
                return 'A new inter-department loan request has been submitted and requires your review.';
            case 'auto_approved':
                return 'An inter-department loan request has been automatically approved by the dean and forwarded for your review.';
            case 'approved':
                return 'An inter-department loan request has been approved.';
            case 'return_initiated':
                return 'A return has been initiated for an inter-department borrowed item and awaits your verification.';
            case 'declined':
                return 'An inter-department loan request has been declined.';
            default:
                return 'An inter-department loan request has been updated.';
        }
    }
}