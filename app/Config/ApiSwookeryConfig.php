<?php

declare(strict_types=1);

namespace App\Config;

readonly class ApiSwookeryConfig
{
    public function __construct(
        public OpenApiConfig $openapi,
        public ServerConfig $server,
        public MiddlewareConfig $middleware,
        public MockingConfig $mocking
    ) {}

    public static function fromArray(array $config): self
    {
        return new self(
            openapi: OpenApiConfig::fromArray($config['openapi'] ?? []),
            server: ServerConfig::fromArray($config['server'] ?? []),
            middleware: MiddlewareConfig::fromArray($config['middleware'] ?? []),
            mocking: MockingConfig::fromArray($config['mocking'] ?? [])
        );
    }

    public static function defaults(): self
    {
        return new self(
            openapi: new OpenApiConfig,
            server: new ServerConfig,
            middleware: new MiddlewareConfig,
            mocking: new MockingConfig
        );
    }

    public function toArray(): array
    {
        return [
            'openapi' => $this->openapi->toArray(),
            'server' => $this->server->toArray(),
            'middleware' => $this->middleware->toArray(),
            'mocking' => $this->mocking->toArray(),
        ];
    }
}
