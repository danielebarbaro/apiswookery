<?php

declare(strict_types=1);

namespace App\Generators;

use App\Config\ApiSwookeryConfig;
use cebe\openapi\spec\Schema;
use Faker\Factory;
use Faker\Generator;

class MockDataGenerator
{
    private Generator $faker;

    public function __construct(
        private readonly ApiSwookeryConfig $config
    ) {
        $this->faker = Factory::create();
    }

    public function generate(array|Schema $schema): mixed
    {
        if ($schema instanceof Schema) {
            return $this->generateFromSchema($schema);
        }

        return $this->generateFromArray($schema);
    }

    private function generateFromSchema(Schema $schema): mixed
    {
        // Usa esempio se presente
        if (isset($schema->example)) {
            return $schema->example;
        }

        return match ($schema->type) {
            'object' => $this->generateObject($schema),
            'array' => $this->generateArray($schema),
            'string' => $this->generateString($schema),
            'integer' => $this->generateInteger($schema),
            'number' => $this->generateNumber($schema),
            'boolean' => $this->generateBoolean($schema),
            default => null,
        };
    }

    private function generateFromArray(array $schema): mixed
    {
        if (isset($schema['example'])) {
            return $schema['example'];
        }

        return match ($schema['type'] ?? 'object') {
            'string' => $this->generateStringFromArray($schema),
            'number' => $this->generateNumberFromArray($schema),
            'integer' => $this->generateIntegerFromArray($schema),
            'boolean' => $this->generateBooleanFromArray($schema),
            'array' => $this->generateArrayFromArray($schema),
            'object' => $this->generateObjectFromArray($schema),
            default => null,
        };
    }

    private function generateObject(Schema $schema): array
    {
        if (!isset($schema->properties)) {
            return [];
        }

        $result = [];
        foreach ($schema->properties as $propertyName => $propertySchema) {
            if ($propertySchema instanceof Schema) {
                if (in_array($propertyName, $schema->required ?? [], true) || $this->faker->boolean(80)) {
                    $result[$propertyName] = $this->generate($propertySchema);
                }
            }
        }

        return $result;
    }

    private function generateArray(Schema $schema): array
    {
        if (!isset($schema->items)) {
            return [];
        }

        $minItems = $schema->minItems ?? 1;
        $maxItems = $schema->maxItems ?? 5;
        $count = $this->faker->numberBetween($minItems, $maxItems);

        return array_map(
            fn() => $this->generate($schema->items),
            range(1, $count)
        );
    }

    private function generateString(Schema $schema): string
    {
        if (isset($schema->enum)) {
            return $this->faker->randomElement($schema->enum);
        }

        return match ($schema->format ?? '') {
            'date-time' => $this->faker->dateTime->format(DATE_ATOM),
            'date' => $this->faker->date(),
            'email' => $this->faker->email(),
            'uri' => $this->faker->url(),
            'hostname' => $this->faker->domainName(),
            'ipv4' => $this->faker->ipv4(),
            'ipv6' => $this->faker->ipv6(),
            default => $this->faker->sentence(),
        };
    }

    private function generateInteger(Schema $schema): int
    {
        $minimum = $schema->minimum ?? PHP_INT_MIN;
        $maximum = $schema->maximum ?? PHP_INT_MAX;

        return match ($schema->format ?? '') {
            'int64' => $this->faker->numberBetween((int)$minimum, (int)$maximum),
            default => $this->faker->numberBetween((int)$minimum, (int)$maximum),
        };
    }

    private function generateNumber(Schema $schema): float
    {
        $minimum = $schema->minimum ?? PHP_FLOAT_MIN;
        $maximum = $schema->maximum ?? PHP_FLOAT_MAX;

        return $this->faker->randomFloat(2, (float)$minimum, (float)$maximum);
    }

    private function generateBoolean(Schema $schema): bool
    {
        return $this->faker->boolean();
    }

    // Metodi per la generazione da array (mantenuti per retrocompatibilità)
    private function generateStringFromArray(array $schema): string
    {
        if (isset($schema['enum'])) {
            return $this->faker->randomElement($schema['enum']);
        }

        return match ($schema['format'] ?? '') {
            'date-time' => $this->faker->dateTime->format(DATE_ATOM),
            'date' => $this->faker->date(),
            'email' => $this->faker->email(),
            'uri' => $this->faker->url(),
            'hostname' => $this->faker->domainName(),
            'ipv4' => $this->faker->ipv4(),
            'ipv6' => $this->faker->ipv6(),
            default => $this->faker->sentence(),
        };
    }

    private function generateNumberFromArray(array $schema): float
    {
        $minimum = $schema['minimum'] ?? PHP_FLOAT_MIN;
        $maximum = $schema['maximum'] ?? PHP_FLOAT_MAX;

        return $this->faker->randomFloat(2, (float)$minimum, (float)$maximum);
    }

    private function generateIntegerFromArray(array $schema): int
    {
        $minimum = $schema['minimum'] ?? PHP_INT_MIN;
        $maximum = $schema['maximum'] ?? PHP_INT_MAX;

        return $this->faker->numberBetween((int)$minimum, (int)$maximum);
    }

    private function generateBooleanFromArray(array $schema): bool
    {
        return $this->faker->boolean();
    }

    private function generateArrayFromArray(array $schema): array
    {
        if (!isset($schema['items'])) {
            return [];
        }

        $minItems = $schema['minItems'] ?? 1;
        $maxItems = $schema['maxItems'] ?? 5;
        $count = $this->faker->numberBetween($minItems, $maxItems);

        return array_map(
            fn() => $this->generate($schema['items']),
            range(1, $count)
        );
    }

    private function generateObjectFromArray(array $schema): array
    {
        if (!isset($schema['properties'])) {
            return [];
        }

        $result = [];
        foreach ($schema['properties'] as $propertyName => $propertySchema) {
            if (
                in_array($propertyName, $schema['required'] ?? [], true) ||
                $this->faker->boolean(80)
            ) {
                $result[$propertyName] = $this->generate($propertySchema);
            }
        }

        return $result;
    }
}
