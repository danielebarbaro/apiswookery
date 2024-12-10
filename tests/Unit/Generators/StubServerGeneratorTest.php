<?php

use App\Config\ApiSwookeryConfig;
use App\Generators\MockDataGenerator;
use App\Generators\StubServerGenerator;
use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;

beforeEach(function () {
    $this->config = ApiSwookeryConfig::defaults();
    $this->mockGenerator = new MockDataGenerator($this->config);
    $this->generator = new StubServerGenerator($this->config, $this->mockGenerator);
    $this->fixturesPath = dirname(__DIR__, 2).'/Fixtures';

    $yamlFile = $this->fixturesPath.'/valid-petstore.yaml';
    if (! file_exists($yamlFile)) {
        throw new RuntimeException("Test fixture not found: $yamlFile");
    }

    $this->spec = Reader::readFromYamlFile(
        $yamlFile,
        OpenApi::class,
        ReferenceContext::RESOLVE_MODE_ALL
    );
});

it('generates server config', function () {
    $config = $this->generator->generateServerConfig();

    expect($config)->toBeString()
        ->and($config)->toContain('daemonize')
        ->and($config)->toContain('worker_num')
        ->and($config)->toContain('reload_async')
        ->and($config)->toContain('max_wait_time')
        ->and($config)->toContain('log_level')
        ->and($config)->toContain('log_rotation');
});

it('generates server from OpenAPI spec', function () {
    $serverCode = $this->generator->generate($this->spec);

    expect($serverCode)->toBeString()
        ->and($serverCode)->toContain('OpenSwoole\HTTP\Server')
        ->and($serverCode)->toContain($this->config->server->host)
        ->and($serverCode)->toContain((string) $this->config->server->port)
        ->and($serverCode)->toContain('Available endpoints:')
        ->and($serverCode)->toContain('/pet');
});

it('generates endpoints list', function () {
    $list = $this->generator->generateEndpointsList($this->spec);

    expect($list)->toBeString()
        ->and($list)->toContain('[PUT, POST] | /pet')
        ->and($list)->toContain('[GET] | /health');
});

it('generates request handler with routes', function () {
    $handler = $this->generator->generateRequestHandler($this->spec);

    expect($handler)->toBeString()
        ->and($handler)->toContain('switch ($path)')
        ->and($handler)->toContain('case $method === \'POST\'')
        ->and($handler)->toContain('case $method === \'PUT\'')
        ->and($handler)->toContain('case $path === "/health"');
});

it('includes health check endpoint', function () {
    $list = $this->generator->getServiceRoutes([]);

    expect($list)->toBeArray()
        ->and($list[0])->toContain('/health')
        ->and($list[0])->toContain('[GET]');
});
