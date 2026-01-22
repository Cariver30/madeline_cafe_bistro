<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PosReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public array $receipt;
    public string $downloadUrl;
    public string $stage;

    public function __construct(Order $order, array $receipt, string $downloadUrl, string $stage = 'paid')
    {
        $this->order = $order;
        $this->receipt = $receipt;
        $this->downloadUrl = $downloadUrl;
        $this->stage = $stage;
    }

    public function build(): self
    {
        $subject = $this->stage === 'pre'
            ? 'Cuenta abierta de tu mesa'
            : 'Recibo de tu pedido';

        $mail = $this->subject($subject)
            ->view('emails.pos-receipt');

        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = app('dompdf.wrapper')
                ->loadView('receipts.pos-receipt', [
                    'receipt' => $this->receipt,
                ])
                ->setPaper('letter');

            $mail->attachData(
                $pdf->output(),
                sprintf('%s-%s.pdf', $this->stage === 'pre' ? 'cuenta' : 'recibo', $this->order->id),
                ['mime' => 'application/pdf']
            );
        }

        return $mail;
    }
}
