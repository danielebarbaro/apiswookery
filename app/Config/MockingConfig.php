<?php

declare(strict_types=1);

namespace App\Config;

readonly class MockingConfig
{
    public function __construct(
        public array $defaults = [
            'string' => 'dummy-string',
            'number' => 42,
            'integer' => 1,
            'boolean' => true,
            'array' => [],
            'object' => [],
        ],
        public array $generationPriority = [
            'example',
            'examples',
            'schema',
        ],
        public array $supportedFormats = ['json'],
        public bool $realistic = false
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            defaults: $config['defaults'] ?? [
            'string' => 'dummy-string',
            'number' => 42,
            'integer' => 1,
            'boolean' => true,
            'array' => [],
            'object' => [],
        ],
            generationPriority: $config['generation_priority'] ?? ['example', 'examples', 'schema'],
            supportedFormats: $config['supported_formats'] ?? ['json']
        );
    }

    public function toArray(): array
    {
        return [
            'defaults' => $this->defaults,
            'generation_priority' => $this->generationPriority,
            'supported_formats' => $this->supportedFormats,
            'realistic' => $this->realistic,
        ];
    }
}
