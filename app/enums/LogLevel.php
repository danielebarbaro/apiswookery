<?php

namespace App\enums;

use OpenSwoole\Constant;

enum LogLevel: int
{
    case DEBUG = Constant::LOG_DEBUG;
    case TRACE = Constant::LOG_TRACE;
    case INFO = Constant::LOG_INFO;
    case NOTICE = Constant::LOG_NOTICE;
    case WARNING = Constant::LOG_WARNING;
    case ERROR = Constant::LOG_ERROR;
    case NONE = Constant::LOG_NONE;

    public static function getKeys(): array
    {
        return array_map(fn (self $level) => $level->name, self::cases());
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
