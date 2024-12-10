<?php

namespace App\Commands;

use App\Config\ApiSwookeryConfig;
use App\OpenApi\SpecificationReader;
use cebe\openapi\spec\OpenApi;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

class ValidateCommand extends Command
{
    protected $signature = 'validate
        {spec : Path to your OpenAPI specification}
        {--config= : Path to configuration file}';

    protected $description = '🔍 Validate your OpenAPI specification';

    public function handle(): int
    {
        $this->line('🔍 Validating your magical scroll...');
        $this->newLine();

        try {
            $config = $this->loadConfig();

            $reader = new SpecificationReader($config);

            $specPath = $this->argument('spec');
            if (! $this->validateFile($specPath)) {
                return self::FAILURE;
            }

            $spec = $reader->read($specPath);

            if ($reader->validate($spec)) {
                $this->info('✨ Your magical scroll OpenAPI specification is valid!');
                $this->newLine();
                $this->table(
                    ['Property', 'Value'],
                    [
                        ['Version', $spec->openapi],
                        ['Title', $spec->info->title ?? 'N/A'],
                        ['Endpoints', count($spec->paths)],
                        ['Schemas', count($spec->components?->schemas ?? [])],
                    ]
                );
                $this->showSummary($spec);

                return self::SUCCESS;
            }
        } catch (RuntimeException $e) {
            $this->error('🌋 Your magical scroll has some imperfections:');
            $this->error("  ❌ {$e->getMessage()}");

            return self::FAILURE;
        }

        return self::FAILURE;
    }

    private function loadConfig(): ApiSwookeryConfig
    {
        $configPath = $this->option('config');

        if ($configPath && File::exists($configPath)) {
            return ApiSwookeryConfig::fromArray(require $configPath);
        }

        return ApiSwookeryConfig::defaults();
    }

    private function validateFile(string $path): bool
    {
        if (! File::exists($path)) {
            $this->error('🌋 The magical scroll does not exist: '.$path);

            return false;
        }

        if (! is_readable($path)) {
            $this->error('🌋 Cannot read the magical scroll: '.$path);

            return false;
        }

        return true;
    }

    private function showSummary(OpenApi $openapi): void
    {
        $availableOperations = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];

        $this->newLine();
        $this->line('📖 Specification Summary:');

        if (! isset($openapi->paths)) {
            $this->line('No paths available in the specification.');

            return;
        }

        $this->newLine();
        $this->line('🛣️  OpenAPI Available Paths:');

        foreach ($openapi->paths as $path => $pathItem) {
            $methods = array_filter(
                array_map(
                    fn ($operation) => isset($pathItem->{$operation}) ? strtoupper($operation) : null,
                    $availableOperations
                )
            );

            $this->line(sprintf(
                '  %s [%s]',
                $path,
                implode(', ', $methods)
            ));
        }
    }
}
