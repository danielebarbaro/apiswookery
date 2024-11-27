<?php

namespace App\Generators;

use Faker\Factory;
use Faker\Generator;
use RuntimeException;

class MockDataGenerator
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function generate(array $schema): mixed
    {
        if (isset($schema['example'])) {
            return $schema['example'];
        }

        return match ($schema['type'] ?? 'object') {
            'string' => $this->generateString($schema),
            'integer' => $this->generateInteger($schema),
            'number' => $this->generateNumber($schema),
            'boolean' => $this->faker->boolean(),
            'array' => $this->generateArray($schema),
            'object' => $this->generateObject($schema),
            default => throw new RuntimeException("Unsupported type: {$schema['type']}")
        };
    }

    private function generateString(array $schema): string
    {
        if (isset($schema['enum'])) {
            return $this->faker->randomElement($schema['enum']);
        }

        if (isset($schema['format'])) {
            return match ($schema['format']) {
                'date-time' => $this->faker->iso8601(),
                'date' => $this->faker->date(),
                'time' => $this->faker->time(),
                'email' => $this->faker->email(),
                'uuid' => $this->faker->uuid(),
                'uri' => $this->faker->url(),
                'hostname' => $this->faker->domainName(),
                'ipv4' => $this->faker->ipv4(),
                'ipv6' => $this->faker->ipv6(),
                default => $this->faker->word()
            };
        }

        if (isset($schema['pattern'])) {
            return $this->faker->regexify($schema['pattern']);
        }

        $minLength = $schema['minLength'] ?? 1;
        $maxLength = $schema['maxLength'] ?? 20;

        return $this->faker->lexify(str_repeat('?', $this->faker->numberBetween($minLength, $maxLength)));
    }

    private function generateInteger(array $schema): int
    {
        $minimum = $schema['minimum'] ?? PHP_INT_MIN;
        $maximum = $schema['maximum'] ?? PHP_INT_MAX;

        return $this->faker->numberBetween($minimum, $maximum);
    }

    private function generateNumber(array $schema): float
    {
        $minimum = $schema['minimum'] ?? PHP_FLOAT_MIN;
        $maximum = $schema['maximum'] ?? PHP_FLOAT_MAX;

        return $this->faker->randomFloat(
            nbMaxDecimals: 2,
            min: $minimum,
            max: $maximum
        );
    }

    private function generateArray(array $schema): array
    {
        $minItems = $schema['minItems'] ?? 0;
        $maxItems = $schema['maxItems'] ?? 10;
        $items = $schema['items'] ?? ['type' => 'string'];

        $count = $this->faker->numberBetween($minItems, $maxItems);
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->generate($items);
        }

        return $result;
    }

    private function generateObject(array $schema): array
    {
        if (! isset($schema['properties'])) {
            return [];
        }

        $result = [];
        $required = $schema['required'] ?? [];

        foreach ($schema['properties'] as $property => $propertySchema) {
            if (in_array($property, $required) || $this->faker->boolean(80)) {
                $result[$property] = $this->generate($propertySchema);
            }
        }

        return $result;
    }
}
