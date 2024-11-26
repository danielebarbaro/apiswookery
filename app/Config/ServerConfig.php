<?php

declare(strict_types=1);

namespace App\Config;

readonly class ServerConfig
{
    public function __construct(
        public string $host = '127.0.0.1',
        public int $port = 9501,
        public int $workers = 4,
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            host: $config['host'] ?? '127.0.0.1',
            port: (int) ($config['port'] ?? 9501),
            workers: (int) ($config['workers'] ?? 4),
        );
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'workers' => $this->workers,
        ];
    }
}
