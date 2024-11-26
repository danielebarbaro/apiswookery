<?php

use App\Config\ApiSwookeryConfig;
use App\OpenApi\SpecificationReader;
use cebe\openapi\spec\OpenApi;

beforeEach(function () {
    $this->reader = new SpecificationReader(ApiSwookeryConfig::defaults());
    $this->fixturesPath = __DIR__.'/../Fixtures';
});

it('can read valid yaml specification', function () {
    $spec = $this->reader->read("{$this->fixturesPath}/valid-petstore.yaml");

    expect($spec)
        ->toBeInstanceOf(OpenApi::class)
        ->and($spec->openapi)
        ->toBe('3.0.2');
});

it('can read valid json specification', function () {
    $spec = $this->reader->read("{$this->fixturesPath}/valid-petstore.json");

    expect($spec)
        ->toBeInstanceOf(OpenApi::class)
        ->and($spec->openapi)
        ->toBe('3.0.2');
});

it('throws exception for invalid yaml', function () {
    $this->reader->read("{$this->fixturesPath}/invalid-yaml.yaml");
})->throws(RuntimeException::class);

it('validates openapi version', function () {
    $spec = $this->reader->read("{$this->fixturesPath}/valid-petstore.yaml");

    expect($this->reader->validate($spec))->toBeTrue();
});

it('throws exception for unsupported version', function () {
    $config = ApiSwookeryConfig::fromArray(['openapi' => ['version' => '3.1.0']]);
    $reader = new SpecificationReader($config);

    $spec = $reader->read("{$this->fixturesPath}/valid-petstore.yaml");
    $reader->validate($spec);
})->throws(RuntimeException::class, 'OpenAPI version 3.0.2 is not supported');
