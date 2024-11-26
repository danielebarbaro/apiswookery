<?php

namespace Tests\Unit\Config;

use App\Config\OpenApiConfig;

test('OpenApiConfig has correct defaults', function () {
    $config = new OpenApiConfig;

    expect($config->version)->toBe('3.0');
});

test('OpenApiConfig can be created from array', function () {
    $config = OpenApiConfig::fromArray([
        'version' => '3.0.0',
    ]);

    expect($config->version)->toBe('3.0.0');
});

test('OpenApiConfig can be converted to array', function () {
    $config = new OpenApiConfig('3.0.0', false, false);

    expect($config->toArray())->toBe([
        'version' => '3.0.0',
    ]);
});
