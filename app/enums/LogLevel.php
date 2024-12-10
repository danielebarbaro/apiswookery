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
}
