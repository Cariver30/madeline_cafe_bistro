<?php

namespace App\Support\Loyalty;

use App\Mail\LoyaltyRewardExpiringMail;
use App\Models\LoyaltyRedemption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class LoyaltyExpirationNotifier
{
    /**
     * @var int[]
     */
    protected array $reminderDays = [10, 5, 1];

    public function sendReminders(): int
    {
        $sent = 0;
        $today = now()->startOfDay();

        foreach ($this->reminderDays as $days) {
            $targetDate = $today->copy()->addDays($days)->toDateString();

            $redemptions = LoyaltyRedemption::with(['customer', 'reward'])
                ->where('status', 'pending')
                ->whereDate('expires_at', $targetDate)
                ->get();

            $sent += $this->notifyCollection($redemptions, $days);
        }

        return $sent;
    }

    protected function notifyCollection(Collection $redemptions, int $days): int
    {
        $sent = 0;

        foreach ($redemptions as $redemption) {
            $alreadyNotified = collect($redemption->expiration_notified_days ?? [])
                ->map(fn ($value) => (int) $value)
                ->contains($days);

            if ($alreadyNotified) {
                continue;
            }

            $customer = $redemption->customer;
            if (! $customer?->email || ! $redemption->reward) {
                continue;
            }

            try {
                Mail::to($customer->email)->send(new LoyaltyRewardExpiringMail(
                    $customer,
                    $redemption->reward,
                    $redemption,
                    $days
                ));

                $sent++;
            } catch (\Throwable $e) {
                report($e);
                continue;
            }

            $redemption->expiration_notified_days = array_values(array_unique(array_merge(
                $redemption->expiration_notified_days ?? [],
                [$days]
            )));
            $redemption->save();
        }

        return $sent;
    }
}
