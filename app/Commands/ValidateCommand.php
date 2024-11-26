<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ValidateCommand extends Command
{
    protected $signature = 'validate {spec : Path to your OpenAPI specification}';

    protected $description = '🔍 Validate your OpenAPI specification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
