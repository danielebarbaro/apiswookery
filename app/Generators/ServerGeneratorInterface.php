<?php

namespace App\Generators;

use cebe\openapi\spec\OpenApi;

interface ServerGeneratorInterface
{
    public function generate(OpenApi $spec): string;

    public function generateServerConfig(): string;

    public function generateEndpointsList(OpenApi $spec): string;
}
