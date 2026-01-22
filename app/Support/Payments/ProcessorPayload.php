<?php

namespace App\Support\Payments;

class ProcessorPayload
{
    public static function normalize(string $provider, array $payload): array
    {
        $status = self::resolveStatus($payload);

        return [
            'provider' => $provider,
            'status' => $status,
            'rrn' => self::firstValue($payload, ['rrn', 'RRN', 'pnRef', 'PNRef']),
            'auth_code' => self::firstValue($payload, ['authCode', 'AuthCode']),
            'txn_id' => self::firstValue($payload, [
                'transaction_id',
                'transactionId',
                'txnId',
                'txId',
                'transId',
                'TransNum',
            ]),
            'entry_type' => self::firstValue($payload, [
                'entry_type',
                'EntryType',
                'transaction_mode',
                'transactionMode',
            ]),
            'card_type' => self::firstValue($payload, ['card_type', 'cardType', 'CardType']),
            'payment_type' => self::firstValue($payload, ['paymentType', 'PaymentType']),
            'last4' => self::firstValue($payload, ['last_4_digits', 'last4', 'AcntLast4']),
            'resp_code' => self::firstValue($payload, ['respCode', 'ResultCode', 'resultCode', 'HostResponseCode']),
            'raw' => $payload,
        ];
    }

    private static function resolveStatus(array $payload): string
    {
        $status = self::firstValue($payload, ['status', 'Status']);
        if (is_string($status) && $status !== '') {
            $normalized = strtolower($status);
            if (str_contains($normalized, 'approved') || str_contains($normalized, 'success')) {
                return 'approved';
            }
            if (str_contains($normalized, 'declined') || str_contains($normalized, 'failed')) {
                return 'declined';
            }
        }

        $codes = [
            self::firstValue($payload, ['respCode']),
            self::firstValue($payload, ['ResultCode']),
            self::firstValue($payload, ['resultCode']),
            self::firstValue($payload, ['HostResponseCode']),
        ];
        foreach ($codes as $code) {
            if ($code === null || $code === '') {
                continue;
            }
            $codeValue = (string) $code;
            if (in_array($codeValue, ['0', '00'], true)) {
                return 'approved';
            }
        }

        return 'unknown';
    }

    private static function firstValue(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null && $payload[$key] !== '') {
                return (string) $payload[$key];
            }
        }

        return null;
    }
}
