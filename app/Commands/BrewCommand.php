<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class BrewCommand extends Command
{
    protected $signature = 'brew
        {spec : Path to your magical OpenAPI specification}
        {--port=9501 : Which port to enchant}
        {--host=127.0.0.1 : Which realm to bind to}
        {--workers=4 : Number of worker processes}
        {--output=openswoole-server.php : Output file for the generated server}';

    protected $description = '🧙‍♂️ Brew a magical mock server from your OpenAPI specification';

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
