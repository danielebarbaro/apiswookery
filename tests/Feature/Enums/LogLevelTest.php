<?php

use App\enums\LogLevel;

it('provides valid log level cases', function () {
    $cases = LogLevel::cases();

    expect($cases)->toBeArray()
        ->and($cases)->toContain(LogLevel::DEBUG)
        ->and($cases)->toContain(LogLevel::ERROR)
        ->and($cases)->toContain(LogLevel::NONE);
});

it('validates log level keys correctly', function () {
    expect(LogLevel::isValidKey('DEBUG'))->toBeTrue()
        ->and(LogLevel::isValidKey('ERROR'))->toBeTrue()
        ->and(LogLevel::isValidKey('INVALID'))->toBeFalse();
});

it('converts keys to enum cases', function () {
    expect(LogLevel::fromKey('DEBUG'))->toBe(LogLevel::DEBUG)
        ->and(LogLevel::fromKey('ERROR'))->toBe(LogLevel::ERROR)
        ->and(LogLevel::fromKey('INVALID'))->toBeNull();
});
