<?php

namespace App\OpenApi;

use App\Config\ApiSwookeryConfig;
use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
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
        $absolutePath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$path;

        if (! File::exists($path)) {
            throw new RuntimeException("OpenAPI specification file not found: {$path}");
        }

        // ALL: resolve all references, which will result in a large description with a lot of repetitions
        // but no references (except if there are recursive references, these will stop at some level)
        $mode = ReferenceContext::RESOLVE_MODE_ALL;

        // INLINE: only references to external files are resolved, references to places in the current file
        // are still Reference objects.
        // $mode = ReferenceContext::RESOLVE_MODE_INLINE;

        return match (File::extension($path)) {
            'yaml', 'yml' => Reader::readFromYamlFile($absolutePath, OpenApi::class, $mode),
            'json' => Reader::readFromJsonFile($absolutePath, OpenApi::class, $mode),
            default => throw new RuntimeException('Unsupported specification format. Use YAML or JSON.'),
        };
    }

    public function validate(OpenApi $openApi): bool
    {
        if (! $openApi->validate()) {
            $errors = $openApi->getErrors();
            throw new RuntimeException(
                "Invalid OpenAPI specification:\n".implode("\n", array_map(
                    fn ($error) => "- {$error}",
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
