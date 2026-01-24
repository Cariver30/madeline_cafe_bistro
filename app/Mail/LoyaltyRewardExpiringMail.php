<?php

namespace App\Mail;

use App\Models\LoyaltyCustomer;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyReward;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoyaltyRewardExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public LoyaltyCustomer $customer;
    public LoyaltyReward $reward;
    public LoyaltyRedemption $redemption;
    public int $daysRemaining;

    public function __construct(
        LoyaltyCustomer $customer,
        LoyaltyReward $reward,
        LoyaltyRedemption $redemption,
        int $daysRemaining
    ) {
        $this->customer = $customer;
        $this->reward = $reward;
        $this->redemption = $redemption;
        $this->daysRemaining = $daysRemaining;
    }

    public function build(): self
    {
        return $this->subject('Tu recompensa está por expirar')
            ->view('emails.loyalty.reward-expiring');
    }
}
