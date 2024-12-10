<?php

use App\Config\ApiSwookeryConfig;
use App\Generators\MockDataGenerator;
use cebe\openapi\spec\Schema;

beforeEach(function () {
    $config = ApiSwookeryConfig::defaults();
    $this->generator = new MockDataGenerator($config);
});

it('generates data from schema with example', function () {
    $schema = new Schema([
        'type' => 'string',
        'example' => 'test-example'
    ]);

    $result = $this->generator->generate($schema);

    expect($result)->toBe('test-example');
});

it('generates data from array with example', function () {
    $schema = [
        'type' => 'string',
        'example' => 'test-example'
    ];

    $result = $this->generator->generate($schema);

    expect($result)->toBe('test-example');
});

it('generates object data', function () {
    $schema = new Schema([
        'type' => 'object',
        'properties' => [
            'name' => ['type' => 'string'],
            'age' => ['type' => 'integer'],
            'email' => ['type' => 'string', 'format' => 'email']
        ],
        'required' => ['name', 'email']
    ]);

    $result = $this->generator->generate($schema);

    expect($result)
        ->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('email')
        ->and($result['name'])->toBeString()
        ->and($result['email'])->toContain('@');
});

it('generates array data', function () {
    $schema = new Schema([
        'type' => 'array',
        'items' => [
            'type' => 'string'
        ],
        'minItems' => 2,
        'maxItems' => 4
    ]);

    $result = $this->generator->generate($schema);

    expect($result)
        ->toBeArray()
        ->each->toBeString();
});

it('generates valid date-time string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'date-time'
    ]);

    $result = $this->generator->generate($schema);
    expect(DateTime::createFromFormat(DATE_ATOM, $result))->not->toBeFalse();
});

it('generates valid date string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'date'
    ]);

    $result = $this->generator->generate($schema);
    expect(DateTime::createFromFormat('Y-m-d', $result))->not->toBeFalse();
});

it('generates valid email string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'email'
    ]);

    $result = $this->generator->generate($schema);
    expect($result)->toMatch('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
});

it('generates valid uri string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'uri'
    ]);

    $result = $this->generator->generate($schema);
    expect($result)->toMatch('/^https?:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/\S*)?$/');
});

it('generates valid uuid string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'uuid'
    ]);

    $result = $this->generator->generate($schema);
    expect($result)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

it('generates valid hostname string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'hostname'
    ]);

    $result = $this->generator->generate($schema);
    expect($result)->toMatch('/^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/');
});

it('generates valid ipv4 string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'ipv4'
    ]);

    $result = $this->generator->generate($schema);
    expect($result)->toMatch('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/');
});

it('generates valid ipv6 string', function () {
    $schema = new Schema([
        'type' => 'string',
        'format' => 'ipv6'
    ]);

    $result = $this->generator->generate($schema);
    expect($result)->toMatch('/^(?:[A-F0-9]{1,4}:){7}[A-F0-9]{1,4}$/i');
});

it('generates string from enum', function () {
    $schema = new Schema([
        'type' => 'string',
        'enum' => ['one', 'two', 'three']
    ]);

    $result = $this->generator->generate($schema);

    expect($result)->toBeIn(['one', 'two', 'three']);
});

it('generates integer within bounds', function () {
    $schema = new Schema([
        'type' => 'integer',
        'minimum' => 10,
        'maximum' => 20
    ]);

    $result = $this->generator->generate($schema);

    expect($result)
        ->toBeInt()
        ->toBeGreaterThanOrEqual(10)
        ->toBeLessThanOrEqual(20);
});

it('generates number within bounds', function () {
    $schema = new Schema([
        'type' => 'number',
        'minimum' => 10.5,
        'maximum' => 20.5
    ]);

    $result = $this->generator->generate($schema);

    expect($result)
        ->toBeFloat()
        ->toBeGreaterThanOrEqual(10.5)
        ->toBeLessThanOrEqual(20.5);
});

it('generates boolean values', function () {
    $schema = new Schema([
        'type' => 'boolean'
    ]);

    $result = $this->generator->generate($schema);

    expect($result)->toBeBool();
});

it('generates nested objects', function () {
    $schema = new Schema([
        'type' => 'object',
        'required' => ['user'],
        'properties' => [
            'user' => [
                'type' => 'object',
                'required' => ['name', 'address'],
                'properties' => [
                    'name' => ['type' => 'string'],
                    'address' => [
                        'type' => 'object',
                        'required' => ['street', 'city'],
                        'properties' => [
                            'street' => ['type' => 'string'],
                            'city' => ['type' => 'string']
                        ]
                    ]
                ]
            ]
        ]
    ]);

    $result = $this->generator->generate($schema);

    expect($result)
        ->toBeArray()
        ->toHaveKey('user')
        ->and($result['user'])->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('address')
        ->and($result['user']['address'])->toBeArray()
        ->toHaveKeys(['street', 'city']);
});

it('generates array of objects', function () {
    $schema = new Schema([
        'type' => 'array',
        'items' => [
            'type' => 'object',
            'required' => ['id', 'name'],
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string']
            ]
        ],
        'minItems' => 2,
        'maxItems' => 3
    ]);

    $result = $this->generator->generate($schema);

    expect($result)
        ->toBeArray()
        ->each->toBeArray()
        ->each->toHaveKeys(['id', 'name']);
});

it('returns empty array for object without properties', function () {
    $schema = new Schema([
        'type' => 'object'
    ]);

    $result = $this->generator->generate($schema);

    expect($result)->toBeArray()->toBeEmpty();
});

it('returns empty array for array without items', function () {
    $schema = new Schema([
        'type' => 'array'
    ]);

    $result = $this->generator->generate($schema);

    expect($result)->toBeArray()->toBeEmpty();
});

it('returns null for unknown type', function () {
    $schema = new Schema([
        'type' => 'unknown'
    ]);

    $result = $this->generator->generate($schema);

    expect($result)->toBeNull();
});
