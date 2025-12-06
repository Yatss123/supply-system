<?php

namespace App\Notifications;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierOrderNotification extends Notification
{
    use Queueable;

    /** @var Supplier */
    protected $supplier;

    /** @var array<int, array{ supply_name: string, quantity: int, unit: string }>> */
    protected $items;

    /** @var User|null */
    protected $requestedBy;

    /**
     * @param Supplier $supplier
     * @param array $items Each item: ['supply_name' => string, 'quantity' => int, 'unit' => string]
     * @param User|null $requestedBy
     */
    public function __construct(Supplier $supplier, array $items, ?User $requestedBy = null)
    {
        $this->supplier = $supplier;
        $this->items = $items;
        $this->requestedBy = $requestedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $contactName = $this->supplier->contact_person ?: $this->supplier->name;

        $mail = (new MailMessage)
            ->subject('Supply Order Request')
            ->greeting('Hello ' . ($contactName ?: 'Supplier'))
            ->line('Please supply the following items:');

        foreach ($this->items as $item) {
            $name = (string) ($item['supply_name'] ?? 'Item');
            $qty = (int) ($item['quantity'] ?? 0);
            $unit = (string) ($item['unit'] ?? '');
            $mail->line("• {$name} — {$qty} {$unit}");
        }

        if ($this->requestedBy) {
            $mail->line('Requested by: ' . $this->requestedBy->name . ' (' . $this->requestedBy->email . ')');
        }

        if ($this->supplier->address1 || $this->supplier->address2) {
            $addr = trim((string) ($this->supplier->address1 ?? '') . ' ' . (string) ($this->supplier->address2 ?? ''));
            if ($addr !== '') {
                $mail->line('Delivery Address: ' . $addr);
            }
        }

        return $mail->line('Thank you for your partnership.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'supplier_id' => $this->supplier->id,
            'item_count' => count($this->items),
        ];
    }
}