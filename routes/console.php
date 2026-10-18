<?php

use App\Console\Commands\MarkAbsentAsAlpha;
use App\Jobs\RunDailyReconciliationJob;
use App\Support\ModuleGate;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- Modul Tiket & SLA (dimatikan saat modules.ticketing tidak aktif) ---
if (ModuleGate::isEnabled('ticketing')) {
    Schedule::command('sla:evaluate --queue')->everyFifteenMinutes()->withoutOverlapping(10);
}

Schedule::call(function () {
    Cache::put('mstore.scheduler.heartbeat_at', now()->toDateTimeString(), now()->addMinutes(10));
})->name('mstore:scheduler-heartbeat')->everyMinute()->withoutOverlapping(1);

// Schedule Attendance: Mark Absent as Alpha (run every minute)
Schedule::command('attendance:mark-alpha')
    ->everyMinute()
    ->withoutOverlapping(10);

// Schedule Daily Ledger Reconciliation
Schedule::job(new RunDailyReconciliationJob())
    ->dailyAt('00:05')
    ->name('ledger:daily-reconciliation')
    ->withoutOverlapping(30);

tap(
    Schedule::command('logs:prune-sensitive')
        ->dailyAt((string) config('log_retention.scheduler.run_at', '02:25'))
        ->withoutOverlapping((int) config('log_retention.scheduler.without_overlapping_minutes', 30)),
    function ($event) {
        if ((bool) config('log_retention.scheduler.on_one_server', true)) {
            $event->onOneServer();
        }
    }
);

// Register command manually
Artisan::registerCommand(new MarkAbsentAsAlpha());
