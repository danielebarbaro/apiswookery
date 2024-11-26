<?php

declare(strict_types=1);

namespace App\Config;

readonly class OpenApiConfig
{
    public function __construct(
        public string $version = '3.0',
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            version: $config['version'] ?? '3.0',
        );
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
        ];
    }
}
