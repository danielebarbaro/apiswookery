 <?php

/**
 * Generated by ApiSwookery 🧙‍♂️
 * Generated at: {date}
 * @see https://github.com/danielebarbaro/apiswookery
 *
 * This file was automatically generated from OpenAPI specification.
 * Do not modify this file directly.
 */

use OpenSwoole\HTTP\Server;
use OpenSwoole\HTTP\Request;
use OpenSwoole\HTTP\Response;
use OpenSwoole\Constant;

$host = '{host}';
$port = '{port}';

$server = new Server($host, $port);

$metricsEnabled = {metricsEnabled};

$mockData = {mockData};

{middlewareSetup}

$server->set({serverConfig});

$server->on('start', {startHandler});

$server->on('request', {requestHandler});

$server->on('workerStart', {workerStartHandler});

$server->start();