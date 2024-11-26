<?php

namespace App\OpenApi;

use App\Config\ApiSwookeryConfig;
use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Illuminate\Support\Facades\File;
use RuntimeException;

readonly class SpecificationReader
{
    public function __construct(
        private ApiSwookeryConfig $config
    ) {}

    public function read(string $path): OpenApi
    {
        if (!File::exists($path)) {
            throw new RuntimeException("OpenAPI specification file not found: {$path}");
        }

        $content = File::get($path);

        return match (File::extension($path)) {
            'yaml', 'yml' => Reader::readFromYaml($content),
            'json' => Reader::readFromJson($content),
            default => throw new RuntimeException('Unsupported specification format. Use YAML or JSON.'),
        };
    }

    public function validate(OpenApi $openApi): bool
    {
        if (!$openApi->validate()) {
            $errors = $openApi->getErrors();
            throw new RuntimeException(
                "Invalid OpenAPI specification:\n" . implode("\n", array_map(
                    fn($error) => "- {$error}",
                    $errors
                ))
            );
        }

        $version = $openApi->openapi;
        $configVersion = $this->config->openapi->version;

        if (version_compare($version, $configVersion, '<')) {
            throw new RuntimeException(
                "OpenAPI version {$version} is not supported. Minimum required version is {$configVersion}"
            );
        }

        return true;
    }
}
