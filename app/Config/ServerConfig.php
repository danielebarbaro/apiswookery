<?php

declare(strict_types=1);

namespace App\Config;

use OpenSwoole\Constant;

readonly class ServerConfig
{
    public function __construct(
        public string $host = '127.0.0.1',
        public int $port = 9501,
        public int $workers = 4,
        public bool $demonize = false,
        public bool $reload = false,
        public int $logLevel = Constant::LOG_NONE,
        public int $logRotation = Constant::LOG_ROTATION_DAILY,
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            host: $config['host'] ?? '127.0.0.1',
            port: (int) ($config['port'] ?? 9501),
            workers: (int) ($config['workers'] ?? 4),
            demonize: (bool) ($config['demonize'] ?? false),
            reload: (bool) ($config['reload'] ?? false),
            logLevel: (int) ($config['logLevel'] ?? Constant::LOG_NONE),
            logRotation: (int) ($config['logRotation'] ?? Constant::LOG_ROTATION_DAILY),
        );
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'workers' => $this->workers,
            'demonize' => $this->demonize,
            'reload' => $this->reload,
            'logLevel' => $this->logLevel,
            'logRotation' => $this->logRotation,
        ];
    }
}
