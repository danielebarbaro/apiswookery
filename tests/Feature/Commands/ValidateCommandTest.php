<?php

beforeEach(function () {
    $this->fixturesPath = 'tests/Fixtures';
    $this->outputPath = dirname(__DIR__).'/openswoole-server-test.php';
});

it('validates correct specification', function () {
    $this->artisan('validate', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
    ])
        ->expectsOutput('🔍 Validating your magical scroll...')
        ->expectsOutput('✨ Your magical scroll OpenAPI specification is valid!')
        ->assertExitCode(0);
});

it('shows detailed summary for valid specification', function () {
    $this->artisan('validate', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
    ])
        ->expectsOutput('📖 Specification Summary:')
        ->expectsOutput('🛣️  OpenAPI Available Paths:')
        ->expectsOutput('  /pet [PUT, POST]')
        ->assertExitCode(0);
});

it('fails for non-existent specification file', function () {
    $nonExistentPath = "{$this->fixturesPath}/non-existent.yaml";

    $this->artisan('validate', ['spec' => $nonExistentPath])
        ->expectsOutput('🔍 Validating your magical scroll...')
        ->expectsOutput('🌋 The magical scroll does not exist: '.$nonExistentPath)
        ->assertExitCode(1);
});

it('fails for invalid yaml', function () {
    $this->artisan('validate', [
        'spec' => "{$this->fixturesPath}/invalid-yaml.yaml",
    ])
        ->expectsOutput('🔍 Validating your magical scroll...')
        ->expectsOutput('🌋 Your magical scroll has some imperfections:')
        ->assertExitCode(1);
});
