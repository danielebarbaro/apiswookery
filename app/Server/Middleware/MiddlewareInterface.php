<?php

namespace App\Server\Middleware;

use OpenSwoole\HTTP\Request;
use OpenSwoole\HTTP\Response;

interface MiddlewareInterface
{
    public function process(Request $request, Response $response): void;
}
