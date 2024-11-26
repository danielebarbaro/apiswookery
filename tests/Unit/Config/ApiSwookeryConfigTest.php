<?php

namespace Tests\Unit\Config;

use App\Config\ApiSwookeryConfig;
use App\Config\MiddlewareConfig;
use App\Config\OpenApiConfig;
use App\Config\ServerConfig;

test('ApiSwookeryConfig can be created with defaults', function () {
    $config = ApiSwookeryConfig::defaults();

    expect($config)->toBeInstanceOf(ApiSwookeryConfig::class)
        ->and($config->openapi)->toBeInstanceOf(OpenApiConfig::class)
        ->and($config->server)->toBeInstanceOf(ServerConfig::class)
        ->and($config->middleware)->toBeInstanceOf(MiddlewareConfig::class);
});

test('ApiSwookeryConfig can be created from array', function () {
    $config = ApiSwookeryConfig::fromArray([
        'server' => [
            'host' => '0.0.0.0',
            'port' => 8080,
        ],
    ]);

    expect($config->server->host)->toBe('0.0.0.0')
        ->and($config->server->port)->toBe(8080);
});

it('creates default configuration', function () {
    $config = ApiSwookeryConfig::defaults();

    expect($config->server->host)->toBe('127.0.0.1')
        ->and($config->server->port)->toBe(9501)
        ->and($config->server->workers)->toBe(4)
        ->and($config->middleware->logging->enabled)->toBeFalse();
});
