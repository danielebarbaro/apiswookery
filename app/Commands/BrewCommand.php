<?php

namespace App\Commands;

use App\Config\ApiSwookeryConfig;
use App\Config\ServerConfig;
use App\enums\LogLevel;
use App\enums\LogRotation;
use App\Generators\MockDataGenerator;
use App\Generators\StubServerGenerator;
use App\OpenApi\SpecificationReader;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;
use Symfony\Component\Process\Process;

class BrewCommand extends Command
{
    protected $signature = 'brew
        {spec : Path to your magical OpenAPI specification}
        {--config= : Path to configuration file}
        {--port=9501 : Which port to enchant}
        {--host=127.0.0.1 : Which realm to bind to}
        {--workers=4 : Number of worker processes}
        {--demonize=false : Enable demonize mode}
        {--reload=false : Enable asynchronous reloading}
        {--logLevel=NONE : Set log level}
        {--logRotation=ROTATION_DAILY : Set log rotation}
        {--output=openswoole-server.php : Output file for the generated server}';

    protected $description = '🧙‍♂️ Brew a magical mock server from your OpenAPI specification';

    public function handle(): int
    {
        $this->line('🧙‍♂️ Starting the magical brewing process...');
        $this->newLine();

        $logLevel = $this->option('logLevel');
        $logRotation = $this->option('logRotation');

        if (! LogLevel::isValidKey($logLevel)) {
            $this->error("Invalid log level: $logLevel. Allowed values are: ".LogLevel::getCases());

            return self::FAILURE;
        }

        $level = LogLevel::fromKey($logLevel);

        if (! LogRotation::isValidKey($logRotation)) {
            $this->error("Invalid log rotation: $logRotation. Allowed values are: ".LogRotation::getCases());

            return self::FAILURE;
        }

        $rotation = LogRotation::fromKey($logRotation);

        try {
            // Load configuration
            $config = $this->loadConfig();

            // Override config with command options
            $config->server = new ServerConfig(
                host: $this->option('host'),
                port: (int) $this->option('port'),
                workers: (int) $this->option('workers'),
                demonize: (bool) $this->option('demonize'),
                reload: (bool) $this->option('reload'),
                logLevel: (int) $level->value,
                logRotation: (int) $rotation->value,
            );

            // Initialize components
            $reader = new SpecificationReader($config);
            $mockGenerator = new MockDataGenerator($config);
            // TODO: Review the class format -> $serverGenerator = new StubServerGenerator($config, $mockGenerator);
            $serverGenerator = new StubServerGenerator($config, $mockGenerator);

            // Read and validate specification
            $this->info('📖 Reading your magical scroll...');
            $spec = $reader->read($this->argument('spec'));

            if (! $reader->validate($spec)) {
                throw new RuntimeException('Invalid OpenAPI specification');
            }

            // Generate server code
            $this->info('🪄  Brewing your mock server...');
            $serverCode = $serverGenerator->generate($spec);

            // Write to file
            $outputPath = $this->option('output');
            File::put($outputPath, $serverCode);

            $this->newLine();
            $this->info('✨ Your magical mock server has been brewed!');
            $this->table(
                ['Property', 'Value'],
                [
                    ['Server', $config->server->host.':'.$config->server->port],
                    ['Workers', $config->server->workers],
                    ['Demonize', $config->server->demonize === true ? 'TRUE' : 'FALSE'],
                    ['Reload', $config->server->reload === true ? 'TRUE' : 'FALSE'],
                    ['Log Level', $config->server->logLevel],
                    ['Log Rotation', $config->server->logRotation],
                    ['Output', $outputPath],
                ]
            );

            $this->newLine();
            $this->line('To start your server, run:');
            $this->line("php {$outputPath}");
            $this->lintOutput($outputPath);

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error('🌋 Something went wrong during the brewing process:');
            $this->error("  ❌ {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    private function loadConfig(): ApiSwookeryConfig
    {
        $configPath = $this->option('config');

        if ($configPath && File::exists($configPath)) {
            $this->info('📚 Loading configuration from: '.$configPath);

            return ApiSwookeryConfig::fromArray(require $configPath);
        }

        $this->info('📚 Using default configuration');

        return ApiSwookeryConfig::defaults();
    }

    private function lintOutput(string $outputPath): void
    {
        $pintPath = base_path('vendor/bin/pint');

        if (! file_exists($pintPath)) {
            $this->error('Laravel Pint is not installed. Run "composer require laravel/pint".');

            return;
        }

        $process = new Process([$pintPath, '-q', '-n', $outputPath]);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error($process->getErrorOutput());

            return;
        }

    }
}
