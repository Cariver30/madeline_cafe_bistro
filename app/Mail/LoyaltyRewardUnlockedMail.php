<?php

namespace App\Mail;

use App\Models\LoyaltyCustomer;
use App\Models\LoyaltyReward;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoyaltyRewardUnlockedMail extends Mailable
{
    use Queueable, SerializesModels;

    public LoyaltyCustomer $customer;
    public LoyaltyReward $reward;
    public ?Setting $settings;

    public function __construct(LoyaltyCustomer $customer, LoyaltyReward $reward, ?Setting $settings = null)
    {
        $this->customer = $customer;
        $this->reward = $reward;
        $this->settings = $settings;
    }

    public function build(): self
    {
        return $this->subject('¡Has desbloqueado una recompensa!')
            ->view('emails.loyalty.reward-unlocked');
    }
}
