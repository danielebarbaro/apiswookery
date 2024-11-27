<?php

declare(strict_types=1);

namespace App\Generators;

use App\Config\ApiSwookeryConfig;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

class ServerGenerator
{
    public function __construct(
        private readonly ApiSwookeryConfig $config,
        private readonly MockDataGenerator $mockGenerator
    ) {}

    public function generate(OpenApi $spec): string
    {
        $file = new PhpFile;
        $file->setStrictTypes();

        $namespace = $this->createBaseNamespace($file);
        $class = $this->createServerClass($namespace);

        $this->addServerProperties($class);
        $this->addServerConstructor($class);
        $this->addRequestHandler($class);
        $this->addPathHandlers($class, $spec);
        $this->addUtilityMethods($class);

        return (new PsrPrinter)->printFile($file).$this->generateStartupCode($spec);
    }

    private function generateStartupCode(OpenApi $spec): string
    {
        $endpoints = $this->generateEndpointsList($spec);
        $workers = (int) $this->config->server->workers;
        $host = new Literal((string) $this->config->server->host);
        $port = (int) $this->config->server->port;

        return <<<PHP
echo sprintf("\n🚀 OpenSwoole Mock Server v%s\n", OPENSWOOLE_VERSION);

echo sprintf("Server running at http://%s:%d\n\n", '{$host}', {$port});

echo "Available endpoints:\n";
{$endpoints}

echo "  - [GET] | /health\n\n";

\$server = new OpenSwooleServer('{$host}', {$port}, {$workers});
\$server->run();

PHP;
    }

    private function generateEndpointsList(OpenApi $spec): string
    {
        $endpointsList = '';

        foreach ($spec->paths as $path => $pathItem) {
            if (! $pathItem instanceof PathItem) {
                continue;
            }

            $methods = array_keys($pathItem->getOperations());
            $endpointsList .= sprintf(
                'echo "  - [%s] | %s\n";',
                mb_strtoupper(implode(', ', $methods)),
                $path
            )."\n";
        }

        return $endpointsList;
    }

    private function createBaseNamespace(PhpFile $file): PhpNamespace
    {
        $namespace = $file->addNamespace('ApiSwookery\\Server');

        $namespace->addUse('OpenSwoole\\Http\\Server');
        $namespace->addUse('OpenSwoole\\Http\\Request');
        $namespace->addUse('OpenSwoole\\Http\\Response');
        $namespace->addUse('Psr\\Log\\LoggerInterface');
        $namespace->addUse('Psr\\Log\\NullLogger');
        $namespace->addUse('RuntimeException');

        return $namespace;
    }

    private function createServerClass(PhpNamespace $namespace): ClassType
    {
        return $namespace->addClass('OpenSwooleServer');
    }

    private function addServerProperties(ClassType $class): void
    {
        $class->addProperty('server')
            ->setPrivate()
            ->setType('OpenSwoole\\Http\\Server');

        $class->addProperty('routes')
            ->setPrivate()
            ->setType('array')
            ->setValue([])
            ->addComment('@var array<string, array<string, callable>>');

        $class->addProperty('templates')
            ->setPrivate()
            ->setType('array')
            ->setValue([])
            ->addComment('@var array<string, string>');
    }

    private function addServerConstructor(ClassType $class): void
    {
        $class->addProperty('host')
            ->setPrivate()
            ->setType('string');

        $class->addProperty('port')
            ->setPrivate()
            ->setType('int');

        $class->addProperty('workers')
            ->setPrivate()
            ->setType('int');
        // TODO
        //        $class->addProperty('logger')
        //            ->setPrivate()
        //            ->setType('Psr\\Log\\LoggerInterface')
        //            ->setNullable();

        $constructor = $class->addMethod('__construct');

        $constructor->addParameter('host')
            ->setType('string');

        $constructor->addParameter('port')
            ->setType('int');

        $constructor->addParameter('workers')
            ->setType('int');

        // TODO
        //        $constructor->addParameter('logger')
        //            ->setType('Psr\\Log\\LoggerInterface')
        //            ->setNullable()
        //            ->setDefaultValue(null);

        $host = new Literal((string) $this->config->server->host);
        $port = (int) $this->config->server->port;
        $workers = (int) $this->config->server->workers;

        $constructor->setBody(
<<<'BODY'
$this->host = $host;
$this->port = $port;
$this->workers = $workers;
// $this->logger = $logger ?? new NullLogger();

$this->server = new Server($this->host, $this->port);
$this->server->set([
    'worker_num' => $this->workers
]);

$this->server->on('request', [$this, 'handleRequest']);
BODY
        );
    }

    private function addRequestHandler(ClassType $class): void
    {
        $method = $class->addMethod('handleRequest')
            ->setPublic()
            ->setReturnType('void')
            ->addComment('Handle incoming HTTP request');

        $method->addParameter('request')
            ->setType('OpenSwoole\\Http\\Request');

        $method->addParameter('response')
            ->setType('OpenSwoole\\Http\\Response');

        $method->setBody(
            '$path = $request->server[\'request_uri\'];
$method = strtolower($request->server[\'request_method\']);

// $this->logger->info(\'Request received\', [
//     \'path\' => $path,
//     \'method\' => $method
// ]);

try {
   if ($handlerData = $this->matchRoute($method, $path)) {
        [$handler, $params] = $handlerData;
        $request->params = $params;
        call_user_func($handler, $request, $response);
        return;
    }

    throw new RuntimeException(\'Route not found\', 404);
} catch (RuntimeException $e) {
    $response->status($e->getCode() ?: 500);
    $response->header(\'Content-Type\', \'application/json\');
    $response->end(json_encode([
        \'error\' => $e->getMessage()
    ]));
}'
        );
    }

    private function addPathHandlers(ClassType $class, OpenApi $spec): void
    {
        foreach ($spec->paths as $path => $pathItem) {
            if (! $pathItem instanceof PathItem) {
                continue;
            }

            foreach ($pathItem->getOperations() as $method => $operation) {
                if (! $operation instanceof Operation) {
                    continue;
                }
                $handlerMethod = $this->createOperationHandler($class, $path, $method, $operation);
                $this->registerRoute($class, $path, $method, $handlerMethod->getName());
            }
        }
    }

    private function createOperationHandler(
        ClassType $class,
        string $path,
        string $method,
        Operation $operation
    ): Method {
        $handlerName = sprintf(
            'handle_%s%s',
            ucfirst($method),
            str_replace(['{', '}', '/'], ['', '', '_'], ucwords($path))
        );

        $handler = $class->addMethod(strtolower($handlerName))
            ->setReturnType('void')
            ->setPrivate();

        $handler->addParameter('request')
            ->setType('OpenSwoole\\Http\\Request');

        $handler->addParameter('response')
            ->setType('OpenSwoole\\Http\\Response');

        $responseCases = $this->generateResponseCases($operation);

        $handler->setBody(
            '
$params = array_merge(
    $request->params ?? [],
    $request->get ?? [],
    $request->post ?? []
);

//$this->validateParams($params); // TODO

$responseData = '.$responseCases.';

$response->header(\'Content-Type\', \'application/json\');
$response->end(json_encode($responseData));'
        );

        return $handler;
    }

    private function generateResponseCases(Operation $operation): string
    {
        $cases = [];
        foreach ($operation->responses as $code => $response) {
            if (! $response instanceof Response) {
                continue;
            }

            if (! isset($response->content['application/json'])) {
                continue;
            }

            $schema = $response->content['application/json']->schema ?? null;
            if ($schema === null) {
                continue;
            }

            $schema = $response->content['application/json']->schema ?? null;
            if (! ($schema instanceof Schema)) {
                continue;
            }

            $mockData = $this->mockGenerator->generate($schema);

            $cases[] = var_export([
                'data' => $mockData,
                'code' => (int) $code,
            ], true);
        }

        return ! empty($cases) ? implode(PHP_EOL, $cases) : var_export(['data' => null, 'code' => 204], true);
    }

    private function addUtilityMethods(ClassType $class): void
    {
        $matchRoute = $class->addMethod('matchRoute')
            ->setPrivate()
            ->setReturnType('array|null');

        $matchRoute->addParameter('method')
            ->setType('string');

        $matchRoute->addParameter('path')
            ->setType('string');

        $matchRoute->setBody(
            <<<'BODY'
if (!isset($this->routes[$method])) {
    return null;
}

foreach ($this->routes[$method] as $routePattern => $handler) {
    if (preg_match('/^' . $routePattern . '$/', $path, $matches)) {
        array_shift($matches);
        return [$handler, $matches];
    }
}

return null;
BODY
        );

        $registerRoute = $class->addMethod('registerRoute')
            ->setPrivate()
            ->setReturnType('void');

        $registerRoute->addParameter('path')
            ->setType('string');

        $registerRoute->addParameter('method')
            ->setType('string');

        $registerRoute->addParameter('handler')
            ->setType('string');

        $registerRoute->setBody(
            <<<'BODY'
$method = strtolower($method);

$pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([a-zA-Z0-9_-]+)', $path);
$pattern = str_replace('/', '\/', $pattern);

$this->routes[$method][$pattern] = [$this, $handler];
BODY
        );

        // Add run method
        $run = $class->addMethod('run')
            ->setPublic()
            ->setReturnType('void');

        $run->setBody(
            '
//$this->logger->info(\'Starting server\', [
//    \'host\' => $this->host,
//    \'port\' => $this->port
//]);

$this->server->start();'
        );
    }

    private function registerRoute(ClassType $class, string $path, string $method, string $handler): void
    {
        $constructor = $class->getMethod('__construct');
        $currentBody = $constructor->getBody();

        $constructor->setBody($currentBody.sprintf(
            "\n\$this->registerRoute('%s', '%s', '%s');",
            $path,
            $method,
            $handler
        ));
    }
}
