<?php

use App\Config\ServerConfig;
use OpenSwoole\Constant;

it('ServerConfig has correct defaults', function () {
    $config = new ServerConfig;

    expect($config->host)->toBe('127.0.0.1')
        ->and($config->port)->toBe(9501)
        ->and($config->workers)->toBe(4)
        ->and($config->demonize)->toBeFalse()
        ->and($config->reload)->toBeFalse()
        ->and($config->logLevel)->toBe(Constant::LOG_NONE)
        ->and($config->logRotation)->toBe(Constant::LOG_ROTATION_DAILY);
});

it('ServerConfig can be created from array', function () {
    $config = ServerConfig::fromArray([
        'host' => '0.0.0.0',
        'port' => 8080,
        'workers' => 8,
        'demonize' => true,
        'reload' => true,
        'logLevel' => Constant::LOG_INFO,
        'logRotation' => Constant::LOG_ROTATION_HOURLY,
    ]);

    expect($config->host)->toBe('0.0.0.0')
        ->and($config->port)->toBe(8080)
        ->and($config->workers)->toBe(8)
        ->and($config->demonize)->toBeTrue()
        ->and($config->reload)->toBeTrue()
        ->and($config->logLevel)->toBe(Constant::LOG_INFO)
        ->and($config->logRotation)->toBe(Constant::LOG_ROTATION_HOURLY);
});

it('ServerConfig can be converted to array', function () {
    $config = new ServerConfig(
        '0.0.0.0',
        8080,
        8,
        true,
        true,
        Constant::LOG_INFO,
        Constant::LOG_ROTATION_HOURLY
    );

    expect($config->toArray())->toBe([
        'host' => '0.0.0.0',
        'port' => 8080,
        'workers' => 8,
        'demonize' => true,
        'reload' => true,
        'logLevel' => Constant::LOG_INFO,
        'logRotation' => Constant::LOG_ROTATION_HOURLY,
    ]);
});
