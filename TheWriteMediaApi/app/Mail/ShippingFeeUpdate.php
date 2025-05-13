<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShippingFeeUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $oldTotal;
    public $newTotal;

    public function __construct(Order $order, $oldTotal)
    {
        $this->order = $order;
        $this->oldTotal = $oldTotal;
        $this->newTotal = $order->total;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Shipping Fee Updated for Order #' . $this->order->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shipping-fee-update',
        );
    }
}