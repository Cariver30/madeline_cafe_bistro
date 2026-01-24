<?php

namespace App\Http\Controllers\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyRedemption;
use Illuminate\Http\Request;

class RedemptionController extends Controller
{
    public function show(string $token)
    {
        $redemption = LoyaltyRedemption::with(['customer', 'reward'])
            ->where('qr_token', $token)
            ->firstOrFail();

        $state = $this->resolveState($redemption, true);

        return view('loyalty.redeem', compact('redemption', 'state'));
    }

    public function store(Request $request, string $token)
    {
        $redemption = LoyaltyRedemption::with(['customer', 'reward'])
            ->where('qr_token', $token)
            ->firstOrFail();

        $state = $this->resolveState($redemption, true);

        if ($state !== 'pending') {
            return redirect()
                ->route('loyalty.redeem.show', $redemption->qr_token)
                ->withErrors(['status' => 'Este QR ya no es válido para redimir.']);
        }

        $updated = LoyaltyRedemption::where('id', $redemption->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => $request->user()?->id,
                'redeemed_at' => now(),
            ]);

        if ($updated === 0) {
            return redirect()
                ->route('loyalty.redeem.show', $redemption->qr_token)
                ->withErrors(['status' => 'Este QR ya fue redimido.']);
        }

        return redirect()
            ->route('loyalty.redeem.show', $redemption->qr_token)
            ->with('success', 'Redención validada correctamente.');
    }

    protected function resolveState(LoyaltyRedemption $redemption, bool $markExpired = false): string
    {
        if ($redemption->status === 'approved') {
            return 'approved';
        }

        if ($redemption->status === 'rejected') {
            return 'rejected';
        }

        if ($this->isExpired($redemption)) {
            if ($markExpired && $redemption->status === 'pending') {
                $redemption->update([
                    'status' => 'rejected',
                    'notes' => $redemption->notes ?: 'Expirado',
                ]);
            }

            return 'expired';
        }

        return 'pending';
    }

    protected function isExpired(LoyaltyRedemption $redemption): bool
    {
        if (! $redemption->expires_at) {
            return false;
        }

        return now()->startOfDay()->gt($redemption->expires_at);
    }
}
