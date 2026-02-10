<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

class MobileIpAccess
{
    public static function allows(User $user, string $ip): bool
    {
        $settings = self::settings();

        if (! $settings || ! $settings->mobile_ip_restriction_enabled) {
            return true;
        }

        if ($user->hasRole(['manager'])) {
            return true;
        }

        if (self::emailIsBypassed($user->email, $settings->mobile_ip_bypass_emails)) {
            return true;
        }

        if (! $user->hasRole(['server', 'host'])) {
            return true;
        }

        $allowlist = self::parseList($settings->mobile_ip_allowlist);
        if (empty($allowlist) || $ip === '') {
            return false;
        }

        return self::ipInList($ip, $allowlist);
    }

    private static function settings(): ?Setting
    {
        static $cached = null;
        if ($cached === null) {
            $cached = Setting::first();
        }

        return $cached;
    }

    private static function parseList(?string $raw): array
    {
        if (! $raw) {
            return [];
        }

        $items = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if (! $items) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $items)));
    }

    private static function emailIsBypassed(?string $email, ?string $rawList): bool
    {
        if (! $email) {
            return false;
        }

        $items = self::parseList($rawList);
        if (empty($items)) {
            return false;
        }

        $email = strtolower(trim($email));
        foreach ($items as $item) {
            if (strtolower($item) === $email) {
                return true;
            }
        }

        return false;
    }

    private static function ipInList(string $ip, array $rules): bool
    {
        foreach ($rules as $rule) {
            if (self::ipMatchesRule($ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    private static function ipMatchesRule(string $ip, string $rule): bool
    {
        $rule = trim($rule);
        if ($rule === '') {
            return false;
        }

        if (str_contains($rule, '/')) {
            [$subnet, $mask] = array_pad(explode('/', $rule, 2), 2, null);
            if ($subnet === null || $mask === null || ! is_numeric($mask)) {
                return false;
            }
            return self::cidrMatch($ip, $subnet, (int) $mask);
        }

        return $ip === $rule;
    }

    private static function cidrMatch(string $ip, string $subnet, int $mask): bool
    {
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);

        if ($ipBin === false || $subnetBin === false) {
            return false;
        }

        if (strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        if ($mask < 0 || $mask > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($mask, 8);
        $remainingBits = $mask % 8;

        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $maskByte = (~(0xff >> $remainingBits)) & 0xff;
        return (ord($ipBin[$fullBytes]) & $maskByte) === (ord($subnetBin[$fullBytes]) & $maskByte);
    }
}
