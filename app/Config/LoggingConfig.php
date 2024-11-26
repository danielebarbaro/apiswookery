<?php

declare(strict_types=1);

namespace App\Config;

readonly class LoggingConfig
{
    public function __construct(
        public bool $enabled = false,
        public array $excludePaths = ['/health'],
        public bool $logBody = true
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            enabled: $config['enabled'] ?? false,
            excludePaths: $config['exclude_paths'] ?? ['/health'],
            logBody: $config['log_body'] ?? true
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'exclude_paths' => $this->excludePaths,
            'log_body' => $this->logBody,
        ];
    }
}
