<?php

use App\Config\ApiSwookeryConfig;
use App\Config\OpenApiConfig;
use App\OpenApi\SpecificationReader;
use cebe\openapi\spec\OpenApi;
use Symfony\Component\Yaml\Exception\ParseException;

beforeEach(function () {
    $this->fixturesPath = 'tests/Fixtures';
    $this->config = ApiSwookeryConfig::defaults();
    $this->reader = new SpecificationReader($this->config);
});

describe('read method', function () {
    it('reads valid yaml specification', function () {
        $spec = $this->reader->read("{$this->fixturesPath}/valid-petstore.yaml");

        expect($spec)
            ->toBeInstanceOf(OpenApi::class)
            ->and($spec->paths->getPaths())->toHaveKey('/pet')
            ->and($spec->openapi)->toBe('3.0.2')
            ->and($spec->info->title)->toBe('Test Pet Store')
            ->and($spec->info->version)->toBe('1.0.0');

        $petPath = $spec->paths->getPath('/pet');
        expect($petPath->post)->not->toBeNull()
            ->and($petPath->post->summary)->toBe('Add a pet')
            ->and($petPath->post->responses['200']->description)->toBe('OK')
            ->and($petPath->put->summary)->toBe('Update a pet')
            ->and($petPath->put->responses['200']->description)->toBe('OK');

        $petSchema = $spec->components->schemas['Pet'];
        expect($petSchema->type)->toBe('object')
            ->and($petSchema->properties)->toHaveKey('id')
            ->and($petSchema->properties)->toHaveKey('name')
            ->and($petSchema->properties['id']->type)->toBe('integer')
            ->and($petSchema->properties['name']->type)->toBe('string');
    });

    it('reads valid json specification', function () {
        $spec = $this->reader->read("{$this->fixturesPath}/valid-petstore.json");

        expect($spec)
            ->toBeInstanceOf(OpenApi::class)
            ->and($spec->paths->getPaths())->toHaveKey('/pet')
            ->and($spec->openapi)->toBe('3.0.2')
            ->and($spec->info->title)->toBe('Test Pet Store')
            ->and($spec->info->version)->toBe('1.0.0');
    });

    it('reads yaml specification with yml extension', function () {
        copy(
            "{$this->fixturesPath}/valid-petstore.yaml",
            "{$this->fixturesPath}/temp-petstore.yml"
        );

        $spec = $this->reader->read("{$this->fixturesPath}/temp-petstore.yml");

        expect($spec)
            ->toBeInstanceOf(OpenApi::class)
            ->and($spec->paths->getPaths())->toHaveKey('/pet');

        unlink("{$this->fixturesPath}/temp-petstore.yml");
    });
});

describe('validate method', function () {
    it('validates specification version', function () {
        $spec = $this->reader->read("{$this->fixturesPath}/valid-petstore.yaml");
        expect($this->reader->validate($spec))->toBeTrue();
    });

    it('fails for unsupported OpenAPI version', function () {
        $config = new ApiSwookeryConfig(
            new OpenApiConfig('3.1.0'),
            $this->config->server,
            $this->config->middleware,
            $this->config->mocking
        );

        $reader = new SpecificationReader($config);
        $spec = $reader->read("{$this->fixturesPath}/valid-petstore.yaml");

        expect(fn () => $reader->validate($spec))
            ->toThrow(
                RuntimeException::class,
                'OpenAPI version 3.0.2 is not supported. Minimum required version is 3.1.0'
            );
    });

    it('fails for invalid OpenAPI specification', function () {
        $invalidSpec = new OpenApi([
            'openapi' => '3.0.2',
            'paths' => [],
        ]);

        expect(fn () => $this->reader->validate($invalidSpec))
            ->toThrow(RuntimeException::class)
            ->and(fn () => $this->reader->validate($invalidSpec))
            ->toThrow(RuntimeException::class, 'Invalid OpenAPI specification');
    });
});

describe('error handling', function () {
    it('fails when file does not exist', function () {
        expect(fn () => $this->reader->read("{$this->fixturesPath}/non-existent.yaml"))
            ->toThrow(RuntimeException::class, 'OpenAPI specification file not found');
    });

    it('fails for unsupported file format', function () {
        file_put_contents("{$this->fixturesPath}/temp.txt", 'invalid content');

        expect(fn () => $this->reader->read("{$this->fixturesPath}/temp.txt"))
            ->toThrow(RuntimeException::class, 'Unsupported specification format. Use YAML or JSON.');

        unlink("{$this->fixturesPath}/temp.txt");
    });

    it('fails for invalid yaml content', function () {
        expect(fn () => $this->reader->read("{$this->fixturesPath}/invalid-yaml.yaml"))
            ->toThrow(ParseException::class);
    });

    it('handles file system errors gracefully', function () {
        mkdir("{$this->fixturesPath}/invalid-path");

        expect(fn () => $this->reader->read("{$this->fixturesPath}/invalid-path"))
            ->toThrow(RuntimeException::class);

        rmdir("{$this->fixturesPath}/invalid-path");
    });
});
