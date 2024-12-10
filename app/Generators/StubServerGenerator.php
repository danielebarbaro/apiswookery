<?php

namespace App\Generators;

use App\Config\ApiSwookeryConfig;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Response;

class StubServerGenerator implements ServerGeneratorInterface
{
    private array $routes = [];

    private string $stub;

    public function __construct(
        private readonly ApiSwookeryConfig $config,
        private readonly MockDataGenerator $mockGenerator
    ) {
        $this->stub = dirname(__DIR__, 1).DIRECTORY_SEPARATOR.'stubs/openswoole-server.php.stub';
    }

    public function generate(OpenApi $spec): string
    {
        if (! file_exists($this->stub)) {
            throw new \RuntimeException('Stub file not found');
        }

        $stub = file_get_contents($this->stub);

        return strtr($stub, [
            '{date}' => date('Y-m-d H:i:s'),
            '{host}' => $this->config->server->host,
            '{port}' => $this->config->server->port,
            '{metricsEnabled}' => 'false',
            '{serverConfig}' => $this->generateServerConfig(),
            '{middlewareSetup}' => $this->generateMiddlewareSetup(),
            '{startHandler}' => $this->generateStartHandler($spec),
            '{requestHandler}' => $this->generateRequestHandler($spec),
            '{workerStartHandler}' => $this->generateWorkerStartHandler(),
            '{mockData}' => $this->generateMockData($spec),
        ]);
    }

    public function generateServerConfig(): string
    {
        return var_export([
            'daemonize' => $this->config->server->demonize,

            'worker_num' => $this->config->server->workers,

            'reload_async' => $this->config->server->reload, // Enable asynchronous reloading
            'max_wait_time' => 30, // Maximum wait time for worker reloading in seconds

            'max_request_execution_time' => 30, // The Maximum time a request can take to execute in seconds

            'pid_file' => '/tmp/apiswookery.pid',

            'log_level' => $this->config->server->logLevel,
            'log_rotation' => $this->config->server->logRotation,

            'log_file' => '/tmp/apiswookery.log',
            'log_date_format' => '%Y-%m-%d %H:%M:%S',

        ], true);
    }

    private function generateMiddlewareSetup(): string
    {
        return $this->config->middleware->logging->enabled ?
            '$middleware = new LoggingMiddleware();' :
            '// No middleware configured';
    }

    private function generateStartHandler(OpenApi $spec): string
    {
        return sprintf(
            'function($server) {
            echo "Available endpoints:\n";
            %s
        }',
            $this->generateEndpointsList($spec)
        );
    }

    private function generateWorkerStartHandler(): string
    {
        return 'function($server, $workerId) {
            echo sprintf("Worker #%d started\n", $workerId);
        }';
    }

    public function generateMockData(OpenApi $spec): string
    {
        $mockData = [];
        foreach ($spec->paths as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                foreach ($operation->responses as $code => $response) {
                    if (isset($response->content['application/json'])) {
                        $schema = $response->content['application/json']->schema;
                        $mockData[$path][$method][$code] = $this->mockGenerator->generate($schema);
                    }
                }
            }
        }

        return var_export($mockData, true);
    }

    public function generateEndpointsList(OpenApi $spec): string
    {
        $list = [];
        foreach ($spec->paths as $path => $pathItem) {
            $methods = array_keys($pathItem->getOperations());
            $list[] = sprintf(
                'echo "  - [%s] | %s\n";',
                strtoupper(implode(', ', $methods)),
                $path
            );
        }

        // Add service endpoints
        $list = $this->getServiceRoutes($list);

        return implode("\n", $list);
    }

    private function generateRouteHandler(Operation $operation): string
    {
        $defaultResponse = null;
        $responses = [];

        foreach ($operation->responses as $code => $response) {
            if ($code === 'default') {
                $defaultResponse = $response;

                continue;
            }
            $responses[$code] = $response;
        }

        if (empty($responses) && $defaultResponse) {
            $responses['200'] = $defaultResponse;
        }

        if (empty($responses)) {
            return '$response->status(204)->end();';
        }

        $responseCode = key($responses);
        $response = $responses[$responseCode];

        if (! $response instanceof Response || ! isset($response->content['application/json'])) {
            return '$response->status(204)->end();';
        }

        $schema = $response->content['application/json']->schema;

        if ($schema === null) {
            return '$response->status(500)->end(\'Missing Response in OpenApi file \');';
        }

        $mockData = $this->mockGenerator->generate($schema);

        return sprintf(
            '$response->header("Content-Type", "application/json");
        $response->status(%d);
        $response->end(json_encode(%s));',
            $responseCode,
            var_export($mockData, true)
        );
    }

    public function generateRequestHandler(OpenApi $spec): string
    {
        $cases = [];
        foreach ($spec->paths as $path => $pathItem) {
            if (! $pathItem instanceof PathItem) {
                continue;
            }

            foreach ($pathItem->getOperations() as $method => $operation) {
                $dynamicSegments = $this->getDynamicSegments($operation);

                if (count($operation->parameters) > 0 && ! empty($dynamicSegments)) {
                    $format = "case \$method === '%s' && preg_match('#%s#', \$path, \$matches):\n    %s\n    break;";
                    $regexp = $this->getRegexpPath($operation, $path, $dynamicSegments);
                } else {
                    $format = "case \$method === '%s' && \$path === '%s':\n    %s\n    break;";
                    $regexp = $path;
                }

                $cases[] = sprintf(
                    $format,
                    strtoupper($method),
                    $regexp,
                    $this->generateRouteHandler($operation)
                );
            }
        }

        return 'function($request, $response) use ($mockData) {
            $path = $request->server[\'request_uri\'];
            $server = $request->server;
            $method = $server[\'request_method\'];

            try {
                switch ($path) {
                    '.implode("\n ", $cases).'
                    case $path === "/health":
                        $response->end(json_encode([
                            "status" => "ok",
                            "version" => OPENSWOOLE_VERSION
                        ]));
                        break;
                    default:
                        $response->status(404);
                        $response->end(json_encode([
                            "error" => "Not Found",
                            "message" => "Endpoint not found"
                        ]));
                        break;
                }
            } catch (Throwable $e) {
                $response->status(500);
                $response->end(json_encode([
                    "error" => "Internal Server Error",
                    "message" => $e->getMessage()
                ]));
            }
        }';
    }

    private function getRegexpPath(Operation $operation, string $path, array $dynamicSegments): string
    {
        $segments = explode('/', $path);
        $staticSegments = array_filter($segments, function ($segment) {
            return $segment !== '' && ! preg_match('#\{.*?\}#', $segment);
        });

        return '^/'.implode('/', array_map(function ($static, $dynamic) {
            return $static.'/'.$dynamic;
        }, $staticSegments, $dynamicSegments)).'$';
    }

    private function getDynamicSegments(Operation $operation): array
    {
        $dynamicSegments = [];
        foreach ($operation->parameters as $parameter) {
            if ($parameter->in === 'path') {
                $dynamicSegments[] = $parameter->schema->type === 'integer' ?
                    '(\d+)' :
                    '(\w+)';
            }
        }

        return $dynamicSegments;
    }

    public function getServiceRoutes(array $list): array
    {
        $list[] = sprintf(
            'echo "  - [%s] | %s\n";',
            'GET',
            '/health'
        );

        return $list;
    }
}
