<?php

namespace App\Console;

use App\Console\Commands\CacheMedia;
use App\Console\Commands\IndexListMelody;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(CacheMedia::class, ['--type' => '6.tuples.json'])
            ->withoutOverlapping()
            ->daily()
            ->appendOutputTo('/var/www/web/storage/logs/cacheTuples.log');
        $schedule->command(IndexListMelody::class)
            ->withoutOverlapping()
            ->daily()
            ->appendOutputTo('/var/www/web/storage/logs/reindexMelody.log');
        $schedule->exec("cat /var/www/web/storage/app/files-to-index.txt "
            . "| sudo -u python ../tools/indexer.py /var/www/melodyindex")
            ->withoutOverlapping()
            ->daily()
            ->appendOutputTo('/var/www/web/storage/logs/reindexMelody.log');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
