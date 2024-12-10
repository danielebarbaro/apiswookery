<?php

namespace Tests\Unit\Config;

use App\Config\LoggingConfig;

it('LoggingConfig has correct defaults', function () {
    $config = new LoggingConfig;

    expect($config->enabled)->toBeFalse()
        ->and($config->excludePaths)->toBe(['/health'])
        ->and($config->logBody)->toBeTrue();
});

it('LoggingConfig can be created from array', function () {
    $config = LoggingConfig::fromArray([
        'enabled' => true,
        'exclude_paths' => ['/test'],
        'log_body' => false,
    ]);

    expect($config->enabled)->toBeTrue()
        ->and($config->excludePaths)->toBe(['/test'])
        ->and($config->logBody)->toBeFalse();
});

test('LoggingConfig can be converted to array', function () {
    $config = new LoggingConfig(true, ['/custom'], false);

    expect($config->toArray())->toBe([
        'enabled' => true,
        'exclude_paths' => ['/custom'],
        'log_body' => false,
    ]);
});
