<?php

namespace App\Support;

use App\Models\TableSession;
use Illuminate\Support\Facades\Cache;

class TableTurnTimeEstimator
{
    public static function estimateTurnMinutes(?int $partySize): int
    {
        $bucket = self::bucketForPartySize($partySize);
        $buckets = self::loadBuckets();

        return $buckets[$bucket] ?? self::defaultMinutes();
    }

    private static function loadBuckets(): array
    {
        $cacheKey = 'turn_time_buckets';
        $ttl = now()->addMinutes((int) env('TURN_TIME_CACHE_MINUTES', 10));

        return Cache::remember($cacheKey, $ttl, function () {
            $lookbackDays = (int) env('TURN_TIME_LOOKBACK_DAYS', 30);
            $cutoff = now()->subDays($lookbackDays);

            $sessions = TableSession::query()
                ->whereNotNull('closed_at')
                ->where('closed_at', '>=', $cutoff)
                ->get(['party_size', 'seated_at', 'created_at', 'closed_at']);

            $buckets = [
                'small' => [],
                'medium' => [],
                'large' => [],
                'xlarge' => [],
            ];

            foreach ($sessions as $session) {
                $start = $session->seated_at ?? $session->created_at;
                if (! $start || ! $session->closed_at) {
                    continue;
                }

                $duration = $session->closed_at->diffInMinutes($start);
                if ($duration <= 0) {
                    continue;
                }

                $bucket = self::bucketForPartySize($session->party_size);
                $buckets[$bucket][] = $duration;
            }

            $averages = [];
            foreach ($buckets as $bucket => $values) {
                $averages[$bucket] = count($values) > 0
                    ? (int) round(array_sum($values) / count($values))
                    : null;
            }

            return array_filter($averages, fn ($value) => $value !== null);
        });
    }

    private static function bucketForPartySize(?int $partySize): string
    {
        $size = max(1, (int) $partySize);
        if ($size <= 2) {
            return 'small';
        }
        if ($size <= 4) {
            return 'medium';
        }
        if ($size <= 6) {
            return 'large';
        }
        return 'xlarge';
    }

    private static function defaultMinutes(): int
    {
        return (int) env('TURN_TIME_DEFAULT_MINUTES', 75);
    }
}
