<?php

namespace Tests\Unit\Config;

use App\Config\MiddlewareConfig;

it('MiddlewareConfig enables all middlewares', function () {
    $config = MiddlewareConfig::fromArray([
        'logging' => ['enabled' => true],
    ]);

    expect($config->logging->enabled)->toBeTrue();
});
