<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('zonix:observability-alerts-delivery')->everyFiveMinutes();
        $schedule->command('zonix:observability-alerts-disputes')->everyFiveMinutes();
        // En local: cada minuto para que TTL cortos (.env) y `schedule:work` prueben sin esperar 5 min.
        // En el resto de entornos: cada 5 min (en prod sigue haciendo falta cron: * * * * * schedule:run).
        if ($this->app->environment('local')) {
            $schedule->command('zonix:expire-pending-payment-orders')->everyMinute();
        } else {
            $schedule->command('zonix:expire-pending-payment-orders')->everyFiveMinutes();
        }
        $schedule->command('zonix:observability-snapshots-delivery')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
