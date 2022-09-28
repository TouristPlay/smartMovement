<?php

namespace App\Console;

use App\Jobs\MessageUpdater;
use App\Jobs\SyncStop;
use App\Jobs\SyncTransport;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            SyncStop::dispatch();
            SyncTransport::dispatch();
        })->dailyAt('03:00')->timezone('Europe/Moscow');

        $schedule->call(function () {
            MessageUpdater::dispatch();
        })->everyMinute()->timezone('Europe/Moscow');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
