<?php

namespace App\Server\Middleware;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use OpenSwoole\HTTP\Request;
use OpenSwoole\HTTP\Response;

class LoggingMiddleware implements MiddlewareInterface
{
    private float $startTime;

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger,
        private array $config = []
    ) {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    public function process(Request $request, Response $response): void
    {
        $this->startTime = microtime(true);

        $this->logRequest($request);

        register_shutdown_function(function () use ($request, $response) {
            $this->logResponse($request, $response);
        });
    }

    private function logRequest(Request $request): void
    {
        if (! $this->shouldLog($request)) {
            return;
        }

        $message = sprintf(
            '→ %s %s',
            $request->server['request_method'],
            $request->server['request_uri']
        );

        $context = [
            'method' => $request->server['request_method'],
            'uri' => $request->server['request_uri'],
            'headers' => $this->filterHeaders($request->header ?? []),
            'query' => $request->get ?? [],
            'client_ip' => $request->server['remote_addr'],
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        if ($this->config['log_body'] && ! empty($request->post)) {
            $context['body'] = $this->filterSensitiveData($request->post);
        }

        $this->logger->info($message, $context);
    }

    private function logResponse(Request $request, Response $response): void
    {
        if (! $this->shouldLog($request)) {
            return;
        }

        $duration = (microtime(true) - $this->startTime) * 1000;

        $statusCode = $response->getStatusCode();

        $message = sprintf(
            '← %s %s - %d (%dms)',
            $request->server['request_method'],
            $request->server['request_uri'],
            $statusCode,
            $duration
        );

        $context = [
            'status' => $statusCode,
            'duration_ms' => round($duration, 2),
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $this->logger->info($message, $context);
    }

    private function shouldLog(Request $request): bool
    {
        if ($request->server['request_uri'] === '/health') {
            return false;
        }

        foreach ($this->config['exclude_paths'] as $path) {
            if (str_starts_with($request->server['request_uri'], $path)) {
                return false;
            }
        }

        return true;
    }

    private function filterHeaders(array $headers): array
    {
        $filtered = [];

        foreach ($headers as $key => $value) {
            if (! in_array(strtolower($key), $this->config['sensitive_headers'])) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function filterSensitiveData(array $data): array
    {
        array_walk_recursive($data, function (&$value, $key) {
            if (in_array(strtolower($key), $this->config['sensitive_fields'])) {
                $value = '******';
            }
        });

        return $data;
    }

    private function getDefaultConfig(): array
    {
        return [
            'log_body' => true,
            'exclude_paths' => ['/health'],
            'sensitive_headers' => [
                'authorization',
                'cookie',
                'x-api-key',
            ],
            'sensitive_fields' => [
                'password',
                'token',
                'secret',
                'credit_card',
            ],
        ];
    }
}
