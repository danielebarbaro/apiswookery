<?php

use App\enums\LogLevel;
use App\enums\LogRotation;

beforeEach(function () {
    $this->fixturesPath = 'tests/Fixtures';
    $this->outputPath = dirname(__DIR__).'/openswoole-server-test.php';
});

it('brews a server from valid specification', function () {

    $this->artisan('brew', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
        '--output' => $this->outputPath,
    ])
        ->expectsOutput('🧙‍♂️ Starting the magical brewing process...')
        ->expectsOutput('✨ Your magical mock server has been brewed!')
        ->assertExitCode(0);

    expect(file_exists($this->outputPath))->toBeTrue()
        ->and(file_get_contents($this->outputPath))->toContain('new Server(');
});

it('fails for invalid specification', function () {
    $this->artisan('brew', [
        'spec' => "{$this->fixturesPath}/invalid-yaml.yaml",
    ])
        ->expectsOutput('🧙‍♂️ Starting the magical brewing process...')
        ->expectsOutput('🌋 Something went wrong during the brewing process:')
        ->assertExitCode(1);
});

it('respects custom port option', function () {
    $customPort = 8080;

    $this->artisan('brew', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
        '--port' => $customPort,
        '--output' => $this->outputPath,
    ])->assertExitCode(0);

    expect(file_get_contents($this->outputPath))->toContain("\$port = '{$customPort}'");
});

it('validates log level option', function () {
    $this->artisan('brew', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
        '--logLevel' => 'INVALID',
    ])
        ->expectsOutput('Invalid log level: INVALID. Allowed values are: '.LogLevel::getCases())
        ->assertExitCode(1);
});

it('validates log rotation option', function () {
    $this->artisan('brew', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
        '--logRotation' => 'INVALID',
    ])
        ->expectsOutput('Invalid log rotation: INVALID. Allowed values are: '.LogRotation::getCases())
        ->assertExitCode(1);
});

it('loads custom config when provided', function () {

    $this->artisan('brew', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
        '--config' => "{$this->fixturesPath}/config.php",
    ])->assertExitCode(0);
});
