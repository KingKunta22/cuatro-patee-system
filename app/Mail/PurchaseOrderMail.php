<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PurchaseOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $pdfData;

    public function __construct($order, $pdfData)
    {
        $this->order = $order;
        $this->pdfData = $pdfData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Purchase Order ' . $this->order->orderNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-order',
            with: [
                'order' => $this->order,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfData, $this->order->orderNumber . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
