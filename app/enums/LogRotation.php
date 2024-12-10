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
}
