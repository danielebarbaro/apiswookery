<?php

declare(strict_types=1);

namespace App\Config;

readonly class MiddlewareConfig
{
    public function __construct(
        public LoggingConfig $logging = new LoggingConfig,
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            logging: LoggingConfig::fromArray($config['logging'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'logging' => $this->logging->toArray(),
        ];
    }
}
