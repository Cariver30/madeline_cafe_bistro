<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Arr;
use App\Models\OrderBatch;
use App\Models\Setting;
use App\Support\CloverClient;
use App\Support\Loyalty\LoyaltyExpirationNotifier;
use App\Support\WaitingListReservationReminder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    app(LoyaltyExpirationNotifier::class)->sendReminders();
})->dailyAt('09:00');

Schedule::call(function () {
    app(WaitingListReservationReminder::class)->sendReminders();
})->everyMinute();

Artisan::command('clover:reconcile-orders {--days=30} {--limit=200} {--dry-run}', function () {
    $days = (int) $this->option('days');
    $limit = (int) $this->option('limit');
    $dryRun = (bool) $this->option('dry-run');

    $settings = Setting::first();
    $client = CloverClient::fromSettings($settings);
    if (! $client) {
        $this->error('No hay credenciales de Clover configuradas.');
        return 1;
    }

    $query = OrderBatch::query()->whereNotNull('clover_order_id');
    if ($days > 0) {
        $query->where('created_at', '>=', now()->subDays($days));
    }
    if ($limit > 0) {
        $query->limit($limit);
    }

    $batches = $query->get();
    if ($batches->isEmpty()) {
        $this->info('No hay órdenes con Clover ID para reconciliar.');
        return 0;
    }

    $stats = [
        'checked' => 0,
        'closed' => 0,
        'not_found' => 0,
        'skipped' => 0,
    ];

    foreach ($batches as $batch) {
        $stats['checked']++;
        $cloverId = (string) $batch->clover_order_id;
        if ($cloverId === '') {
            $stats['skipped']++;
            continue;
        }

        try {
            $order = $client->getOrder($cloverId, '');
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            $isNotFound = str_contains($message, '(404)')
                || str_contains($message, 'Order not found')
                || str_contains($message, 'Not Found');

            if ($isNotFound) {
                $stats['not_found']++;
                if (! $dryRun) {
                    $batch->update([
                        'status' => 'cancelled',
                        'cancelled_at' => $batch->cancelled_at ?? now(),
                        'clover_order_id' => null,
                        'clover_print_event_id' => null,
                    ]);
                }
            } else {
                $this->warn("Clover error para {$cloverId}: {$message}");
            }
            continue;
        }

        $state = Arr::get($order, 'state');
        $paymentState = strtolower((string) Arr::get($order, 'paymentState', ''));
        $totalPaid = (int) Arr::get($order, 'totalPaid', 0);
        $isClosed = ($state && $state !== 'open')
            || ($paymentState !== '' && $paymentState !== 'open')
            || $totalPaid > 0;

        if ($isClosed) {
            $stats['closed']++;
            if (! $dryRun && ! $batch->metered_closed_at) {
                $batch->update([
                    'metered_closed_at' => now(),
                ]);
            }
        }
    }

    $this->info('Reconciliación completada.');
    $this->line('Revisadas: ' . $stats['checked']);
    $this->line('Cerradas: ' . $stats['closed']);
    $this->line('No encontradas: ' . $stats['not_found']);
    $this->line('Saltadas: ' . $stats['skipped']);

    return 0;
})->purpose('Reconciliar órdenes locales con Clover sin bloquear la app');
