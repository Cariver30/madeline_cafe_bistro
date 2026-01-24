<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyRedemption;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoyaltyRedemptionController extends Controller
{
    public function scan(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'confirm' => ['nullable', 'boolean'],
        ]);

        $token = $this->normalizeToken($data['token']);

        $redemption = LoyaltyRedemption::with(['customer', 'reward'])
            ->where('qr_token', $token)
            ->first();

        if (! $redemption) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'QR inválido.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($redemption->status === 'approved') {
            return response()->json([
                'status' => 'approved',
                'message' => 'Este QR ya fue redimido.',
                'redemption' => $this->serializeRedemption($redemption),
            ], Response::HTTP_CONFLICT);
        }

        if ($this->isExpired($redemption)) {
            if ($redemption->status === 'pending') {
                $redemption->update([
                    'status' => 'rejected',
                    'notes' => $redemption->notes ?: 'Expirado',
                ]);
            }

            return response()->json([
                'status' => 'expired',
                'message' => 'El QR está expirado.',
                'redemption' => $this->serializeRedemption($redemption),
            ], Response::HTTP_GONE);
        }

        $confirm = $request->boolean('confirm', true);
        if (! $confirm) {
            return response()->json([
                'status' => 'pending',
                'message' => 'QR válido.',
                'redemption' => $this->serializeRedemption($redemption),
            ]);
        }

        $updated = LoyaltyRedemption::where('id', $redemption->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_by' => $request->user()?->id,
                'redeemed_at' => now(),
            ]);

        if ($updated === 0) {
            $fresh = $redemption->fresh(['customer', 'reward']);

            return response()->json([
                'status' => $fresh?->status ?? 'approved',
                'message' => 'Este QR ya fue redimido.',
                'redemption' => $fresh ? $this->serializeRedemption($fresh) : null,
            ], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'status' => 'approved',
            'message' => 'Redención validada.',
            'redemption' => $this->serializeRedemption($redemption->fresh(['customer', 'reward'])),
        ]);
    }

    protected function isExpired(LoyaltyRedemption $redemption): bool
    {
        if (! $redemption->expires_at) {
            return false;
        }

        return now()->startOfDay()->gt($redemption->expires_at);
    }

    protected function normalizeToken(string $input): string
    {
        $input = trim($input);
        if (str_starts_with($input, 'http://') || str_starts_with($input, 'https://')) {
            $path = parse_url($input, PHP_URL_PATH) ?? '';
            $segments = array_values(array_filter(explode('/', $path)));
            return (string) end($segments);
        }

        if (str_contains($input, '/')) {
            $segments = array_values(array_filter(explode('/', $input)));
            return (string) end($segments);
        }

        return $input;
    }

    protected function serializeRedemption(LoyaltyRedemption $redemption): array
    {
        return [
            'id' => $redemption->id,
            'status' => $redemption->status,
            'expires_at' => $redemption->expires_at?->format('Y-m-d'),
            'redeemed_at' => $redemption->redeemed_at?->toIso8601String(),
            'reward' => $redemption->reward ? [
                'id' => $redemption->reward->id,
                'title' => $redemption->reward->title,
                'description' => $redemption->reward->description,
                'points_required' => $redemption->reward->points_required,
            ] : null,
            'customer' => $redemption->customer ? [
                'id' => $redemption->customer->id,
                'name' => $redemption->customer->name,
                'email' => $redemption->customer->email,
                'phone' => $redemption->customer->phone,
                'points' => $redemption->customer->points,
            ] : null,
        ];
    }
}
