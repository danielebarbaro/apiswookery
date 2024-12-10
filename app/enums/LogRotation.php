<?php

namespace App\enums;

use OpenSwoole\Constant;

enum LogRotation: int
{
    case ROTATION_SINGLE = Constant::LOG_ROTATION_SINGLE;
    case ROTATION_MONTHLY = Constant::LOG_ROTATION_MONTHLY;
    case ROTATION_DAILY = Constant::LOG_ROTATION_DAILY;
    case ROTATION_HOURLY = Constant::LOG_ROTATION_HOURLY;
    case ROTATION_EVERY_MINUTE = Constant::LOG_ROTATION_EVERY_MINUTE;

    public static function getKeys(): array
    {
        return array_map(fn (self $rotation) => $rotation->name, self::cases());
    }

    public static function getCases(): string
    {
        return implode(', ', self::getKeys());
    }

    public static function isValidKey(string $key): bool
    {
        return in_array($key, self::getKeys(), true);
    }

    public static function fromKey(string $key): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $key) {
                return $case;
            }
        }

        return null;
    }
}
