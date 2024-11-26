<?php

namespace Tests\Unit\Config;

use App\Config\MiddlewareConfig;

test('MiddlewareConfig enables all middlewares', function () {
    $config = MiddlewareConfig::fromArray([
        'logging' => ['enabled' => true],
    ]);

    expect($config->logging->enabled)->toBeTrue();
});
