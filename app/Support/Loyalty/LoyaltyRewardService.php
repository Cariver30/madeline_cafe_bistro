<?php

namespace App\Support\Loyalty;

use App\Mail\LoyaltyRewardUnlockedMail;
use App\Models\LoyaltyCustomer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyVisit;
use App\Models\Setting;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LoyaltyRewardService
{
    public function confirmVisit(LoyaltyVisit $visit, array $data): LoyaltyCustomer
    {
        $customer = DB::transaction(function () use ($visit, $data) {
            $customer = LoyaltyCustomer::firstOrCreate(
                ['email' => strtolower($data['email'])],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'points' => 0,
                ]
            );

            $customer->fill([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'points' => $customer->points + $visit->points_awarded,
                'last_visit_at' => now(),
            ])->save();

            $visit->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'customer_snapshot' => $customer->only(['name', 'email', 'phone', 'points']),
            ]);

            return $customer->fresh();
        });

        $this->notifyUnlockedRewards($customer);

        return $customer;
    }

    public function awardFromTableSession(TableSession $session): ?LoyaltyVisit
    {
        if (($session->service_channel ?? 'table') !== 'table') {
            return null;
        }

        if (!$session->guest_email || !$session->guest_name || !$session->guest_phone) {
            return null;
        }

        $settings = Setting::first();
        $points = optional($settings)->loyalty_points_per_visit ?? 0;
        $visit = null;

        $customer = DB::transaction(function () use ($session, $points, &$visit) {
            $customer = LoyaltyCustomer::firstOrCreate(
                ['email' => strtolower($session->guest_email)],
                [
                    'name' => $session->guest_name,
                    'phone' => $session->guest_phone,
                    'points' => 0,
                ]
            );

            $customer->fill([
                'name' => $session->guest_name,
                'phone' => $session->guest_phone,
                'points' => $customer->points + $points,
                'last_visit_at' => now(),
            ])->save();

            $visit = LoyaltyVisit::create([
                'server_id' => $session->server_id,
                'expected_name' => $session->guest_name,
                'expected_email' => strtolower($session->guest_email),
                'expected_phone' => $session->guest_phone,
                'points_awarded' => $points,
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'customer_snapshot' => $customer->only(['name', 'email', 'phone', 'points']),
            ]);

            return $customer->fresh();
        });

        if (!$visit) {
            return null;
        }

        $this->notifyUnlockedRewards($customer);

        return $visit;
    }

    protected function notifyUnlockedRewards(LoyaltyCustomer $customer): void
    {
        $settings = Setting::first();
        $rewards = LoyaltyReward::where('active', true)
            ->orderBy('points_required')
            ->get();

        foreach ($rewards as $reward) {
            if ($customer->points < $reward->points_required) {
                continue;
            }

            $alreadyPending = $customer->redemptions()
                ->where('loyalty_reward_id', $reward->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($alreadyPending) {
                continue;
            }

            $customer->redemptions()->create([
                'loyalty_reward_id' => $reward->id,
                'points_used' => $reward->points_required,
                'status' => 'pending',
            ]);

            try {
                Mail::to($customer->email)->send(new LoyaltyRewardUnlockedMail($customer, $reward, $settings));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
