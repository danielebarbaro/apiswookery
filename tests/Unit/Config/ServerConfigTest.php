<?php

namespace Tests\Unit\Config;

use App\Config\ServerConfig;

test('ServerConfig has correct defaults', function () {
    $config = new ServerConfig;

    expect($config->host)->toBe('127.0.0.1')
        ->and($config->port)->toBe(9501)
        ->and($config->workers)->toBe(4);
});

test('ServerConfig can be created from array', function () {
    $config = ServerConfig::fromArray([
        'host' => '0.0.0.0',
        'port' => 8080,
        'workers' => 8,
    ]);

    expect($config->host)->toBe('0.0.0.0')
        ->and($config->port)->toBe(8080)
        ->and($config->workers)->toBe(8);
});

test('ServerConfig can be converted to array', function () {
    $config = new ServerConfig('0.0.0.0', 8080, 8, true, true);

    expect($config->toArray())->toBe([
        'host' => '0.0.0.0',
        'port' => 8080,
        'workers' => 8,
    ]);
});

//test('ServerConfig validates port range', function() {
//    expect(fn () => new ServerConfig(port: 0))->toThrow(InvalidArgumentException::class)
//        ->and(fn () => new ServerConfig(port: 65536))->toThrow(InvalidArgumentException::class)
//        ->and(fn () => new ServerConfig(port: 8080))->not->toThrow(InvalidArgumentException::class);
//});
//
//test('ServerConfig validates workers range', function() {
//    expect(fn () => new ServerConfig(workers: 0))->toThrow(InvalidArgumentException::class)
//        ->and(fn () => new ServerConfig(workers: 33))->toThrow(InvalidArgumentException::class)
//        ->and(fn () => new ServerConfig(workers: 8))->not->toThrow(InvalidArgumentException::class);
//});
