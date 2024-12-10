<?php

use App\Config\ApiSwookeryConfig;
use App\OpenApi\SpecificationReader;
use Symfony\Component\Yaml\Exception\ParseException;

beforeEach(function () {
    $this->fixturesPath = 'tests/Fixtures';
});

it('reads valid yaml specification', function () {
    $config = ApiSwookeryConfig::defaults();
    $reader = new SpecificationReader($config);

    $spec = $reader->read("{$this->fixturesPath}/valid-petstore.yaml");

    expect($spec)->toBeInstanceOf(\cebe\openapi\spec\OpenApi::class)
        ->and($spec->paths->getPaths())->toHaveKey('/pet')
        ->and($spec->openapi)->toBe('3.0.2');
});

it('reads valid json specification', function () {
    $config = ApiSwookeryConfig::defaults();
    $reader = new SpecificationReader($config);

    $spec = $reader->read("{$this->fixturesPath}/valid-petstore.json");

    expect($spec)->toBeInstanceOf(\cebe\openapi\spec\OpenApi::class)
        ->and($spec->paths->getPaths())->toHaveKey('/pet')
        ->and($spec->openapi)->toBe('3.0.2');
});

it('fails for unsupported file format', function () {
    $config = ApiSwookeryConfig::defaults();
    $reader = new SpecificationReader($config);

    expect(fn () => $reader->read("{$this->fixturesPath}/invalid.txt"))
        ->toThrow(RuntimeException::class, 'Unsupported specification format. Use YAML or JSON.');
});

it('fails for unsupported file', function () {
    $config = ApiSwookeryConfig::defaults();
    $reader = new SpecificationReader($config);

    expect(fn () => $reader->read("{$this->fixturesPath}/invalid-yaml.yaml"))
        ->toThrow(ParseException::class);
});

it('validates specification version', function () {
    $config = ApiSwookeryConfig::defaults();
    $reader = new SpecificationReader($config);
    $spec = $reader->read("{$this->fixturesPath}/valid-petstore.yaml");

    expect($reader->validate($spec))->toBeTrue();
});
