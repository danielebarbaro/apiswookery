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
        if (isset($schema->example)) {
            return $schema->example;
        }

        return $this->generateByType($schema->type, $schema);
    }

    private function generateFromArray(array $schema): mixed
    {
        if (isset($schema['example'])) {
            return $schema['example'];
        }

        return $this->generateByType($schema['type'] ?? 'object', $schema);
    }

    private function generateByType(string $type, array|Schema $schema): mixed
    {
        return match ($type) {
            'object' => $this->generateObject($schema),
            'array' => $this->generateArray($schema),
            'string' => $this->generateString($schema),
            'integer' => $this->generateInteger($schema),
            'number' => $this->generateNumber($schema),
            'boolean' => $this->generateBoolean(),
            default => null,
        };
    }

    private function generateObject(array|Schema $schema): array
    {
        $properties = $schema instanceof Schema ? $schema->properties : $schema['properties'] ?? [];
        if (empty($properties)) {
            return [];
        }

        $result = [];
        foreach ($properties as $propertyName => $propertySchema) {
            $isRequired = $schema instanceof Schema
                ? in_array($propertyName, $schema->required ?? [], true)
                : in_array($propertyName, $schema['required'] ?? [], true);

            if ($isRequired || $this->faker->boolean(80)) {
                $result[$propertyName] = $this->generate($propertySchema);
            }
        }

        return $result;
    }

    private function generateArray(array|Schema $schema): array
    {
        $items = $schema instanceof Schema ? $schema->items : $schema['items'] ?? null;
        if (!$items) {
            return [];
        }

        $minItems = $schema instanceof Schema ? $schema->minItems ?? 1 : $schema['minItems'] ?? 1;
        $maxItems = $schema instanceof Schema ? $schema->maxItems ?? 5 : $schema['maxItems'] ?? 5;
        $count = $this->faker->numberBetween($minItems, $maxItems);

        return array_map(fn () => $this->generate($items), range(1, $count));
    }

    private function generateString(array|Schema $schema): string
    {
        $enum = $schema instanceof Schema ? $schema->enum : $schema['enum'] ?? null;
        if ($enum) {
            return $this->faker->randomElement($enum);
        }

        $format = $schema instanceof Schema ? $schema->format : $schema['format'] ?? '';
        return match ($format) {
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

    private function generateInteger(array|Schema $schema): int
    {
        $minimum = $schema instanceof Schema ? $schema->minimum ?? PHP_INT_MIN : $schema['minimum'] ?? PHP_INT_MIN;
        $maximum = $schema instanceof Schema ? $schema->maximum ?? PHP_INT_MAX : $schema['maximum'] ?? PHP_INT_MAX;

        return $this->faker->numberBetween((int) $minimum, (int) $maximum);
    }

    private function generateNumber(array|Schema $schema): float
    {
        $minimum = $schema instanceof Schema ? $schema->minimum ?? PHP_FLOAT_MIN : $schema['minimum'] ?? PHP_FLOAT_MIN;
        $maximum = $schema instanceof Schema ? $schema->maximum ?? PHP_FLOAT_MAX : $schema['maximum'] ?? PHP_FLOAT_MAX;

        return $this->faker->randomFloat(2, (float) $minimum, (float) $maximum);
    }

    private function generateBoolean(): bool
    {
        return $this->faker->boolean();
    }
}
