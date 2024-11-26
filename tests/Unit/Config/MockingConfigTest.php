<?php

namespace Tests\Unit\Config;

use App\Config\MockingConfig;

test('MockingConfig has correct defaults', function () {
    $config = new MockingConfig;

    expect($config->defaults)->toBe([
        'string' => 'dummy-string',
        'number' => 42,
        'integer' => 1,
        'boolean' => true,
        'array' => [],
        'object' => [],
    ])
        ->and($config->generationPriority)->toBe(['example', 'examples', 'schema'])
        ->and($config->supportedFormats)->toBe(['json']);
});

test('MockingConfig can be created from array', function () {
    $config = MockingConfig::fromArray([
        'defaults' => ['string' => 'test'],
        'generation_priority' => ['schema'],
        'supported_formats' => ['json', 'xml'],
    ]);

    expect($config->defaults)->toBe(['string' => 'test'])
        ->and($config->generationPriority)->toBe(['schema'])
        ->and($config->supportedFormats)->toBe(['json', 'xml']);
});

test('MockingConfig can be converted to array', function () {
    $config = new MockingConfig(
        defaults: ['custom' => 'value'],
        generationPriority: ['schema'],
        supportedFormats: ['json', 'xml']
    );

    expect($config->toArray())->toBe([
        'defaults' => ['custom' => 'value'],
        'generation_priority' => ['schema'],
        'supported_formats' => ['json', 'xml'],
        'realistic' => false,
    ]);
});
