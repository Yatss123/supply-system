<?php

namespace App\Notifications;

use App\Models\SupplyRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplyRequestStatusNotification extends Notification
{
    use Queueable;

    protected SupplyRequest $requestItem;
    protected string $type; // approved | declined

    public function __construct(SupplyRequest $requestItem, string $type)
    {
        $this->requestItem = $requestItem;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/supply-requests/' . $this->requestItem->id);

        $mail = (new MailMessage)
            ->subject('Supply Request ' . ucfirst($this->type))
            ->line('Your supply request item has been ' . $this->type . '.')
            ->line('Item: ' . $this->requestItem->item_name)
            ->line('Quantity: ' . $this->requestItem->quantity . ' ' . $this->requestItem->unit)
            ->line('Status: ' . $this->requestItem->status)
            ->action('View Request Details', $url);

        if ($this->type === 'declined' && $this->requestItem->rejection_reason) {
            $mail->line('Rejection Reason: ' . $this->requestItem->rejection_reason);
        }

        if ($this->requestItem->batch_id) {
            $mail->line('This item is part of a consolidated request.');
        }

        return $mail->line('Thank you for using our supply management system!');
    }
}