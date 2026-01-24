<?php

namespace App\Mail;

use App\Models\LoyaltyCustomer;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoyaltyPointsUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public LoyaltyCustomer $customer;
    public int $pointsAwarded;
    public int $currentPoints;
    public ?Setting $settings;

    public function __construct(LoyaltyCustomer $customer, int $pointsAwarded, int $currentPoints, ?Setting $settings = null)
    {
        $this->customer = $customer;
        $this->pointsAwarded = $pointsAwarded;
        $this->currentPoints = $currentPoints;
        $this->settings = $settings;
    }

    public function build(): self
    {
        return $this->subject('Tus puntos han sido actualizados')
            ->view('emails.loyalty.points-updated');
    }
}
