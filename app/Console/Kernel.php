<?php

namespace App\Console;

use App\ScheduledTasks\ScaleDownTask;
use App\ScheduledTasks\ScaleUpTask;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(new ScaleUpTask)
            ->timezone($this->scheduleTimezone())
            ->dailyAt('11:58')
            ->name('Scale Up');

        $schedule->call(new ScaleDownTask)
            ->timezone($this->scheduleTimezone())
            ->dailyAt('12:00')
            ->name('Scale Down');
    }

    protected function scheduleTimezone(): \DateTimeZone|string|null
    {
        return 'Asia/Jakarta';
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
