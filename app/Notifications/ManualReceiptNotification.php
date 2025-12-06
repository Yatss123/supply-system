<?php

namespace App\Notifications;

use App\Models\ManualReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ManualReceiptNotification extends Notification
{
    use Queueable;

    protected $manualReceipt;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(ManualReceipt $manualReceipt, $type = 'created')
    {
        $this->manualReceipt = $manualReceipt;
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
        $url = url('/manual-receipts/' . $this->manualReceipt->id);
        
        $subject = 'Manual Receipt ' . ucfirst($this->type);
        $line1 = 'A manual receipt has been ' . $this->type . '.';
        
        $mailMessage = (new MailMessage)
                    ->subject($subject)
                    ->line($line1)
                    ->line('Supply: ' . $this->manualReceipt->supply->name)
                    ->line('Quantity: ' . $this->manualReceipt->quantity)
                    ->line('Supplier: ' . ($this->manualReceipt->supplier ? $this->manualReceipt->supplier->name : 'Not specified'))
                    ->line('Receipt Date: ' . $this->manualReceipt->receipt_date->format('F d, Y'))
                    ->action('View Manual Receipt', $url);

        if ($this->manualReceipt->reference_number) {
            $mailMessage->line('Reference Number: ' . $this->manualReceipt->reference_number);
        }

        if ($this->manualReceipt->cost_per_unit) {
            $mailMessage->line('Cost per Unit: $' . number_format($this->manualReceipt->cost_per_unit, 2));
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
            'manual_receipt_id' => $this->manualReceipt->id,
            'type' => $this->type,
            'supply_name' => $this->manualReceipt->supply->name,
            'quantity' => $this->manualReceipt->quantity,
            'supplier_name' => $this->manualReceipt->supplier ? $this->manualReceipt->supplier->name : 'Not specified',
            'receipt_date' => $this->manualReceipt->receipt_date->toDateString(),
        ];
    }
}
