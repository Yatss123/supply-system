<?php

namespace App\Notifications;

use App\Models\RestockRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestockRequestNotification extends Notification
{
    use Queueable;

    protected $restockRequest;
    protected $type;

    public function __construct(RestockRequest $restockRequest, $type)
    {
        $this->restockRequest = $restockRequest;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/restock-requests/' . $this->restockRequest->id);

        return (new MailMessage)
                    ->line('A restock request has been ' . $this->type . '.')
                    ->action('View Restock Request', $url)
                    ->line('Thank you for using our application!');
    }
}
