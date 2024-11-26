<?php

beforeEach(function () {
    $this->fixturesPath = dirname(__DIR__).'/Fixtures';
});

it('validates correct specification', function () {
    $this->artisan('validate', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
    ])
        ->expectsOutput('🔍 Validating your magical scroll...')
        ->expectsOutput('✨ Your magical scroll OpenAPI specification is valid!')
        ->assertExitCode(0);
});

it('fails for non existent file', function () {
    $nonExistentPath = "{$this->fixturesPath}/non-existent.yaml'";

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

it('shows summary for valid specification', function () {
    $this->artisan('validate', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
    ])
        ->expectsOutput('📖 Specification Summary:')
        ->expectsOutput('🛣️  Available Paths:')
        ->expectsOutput('  /pet [PUT, POST]')
        ->assertExitCode(0);
});

it('loads custom config when provided', function () {
    $configPath = "{$this->fixturesPath}/config.php";
    file_put_contents($configPath, '<?php return ["openapi" => ["version" => "3.0"]];');

    $this->artisan('validate', [
        'spec' => "{$this->fixturesPath}/valid-petstore.yaml",
        '--config' => $configPath,
    ])->assertExitCode(0);

    unlink($configPath);
});
