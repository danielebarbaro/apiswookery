<?php

use App\enums\LogRotation;

it('provides valid log rotation cases', function () {
    $cases = LogRotation::cases();

    expect($cases)->toBeArray()
        ->and($cases)->toContain(LogRotation::ROTATION_DAILY)
        ->and($cases)->toContain(LogRotation::ROTATION_HOURLY)
        ->and($cases)->toContain(LogRotation::ROTATION_MONTHLY);
});

it('validates log rotation keys correctly', function () {
    expect(LogRotation::isValidKey('ROTATION_DAILY'))->toBeTrue()
        ->and(LogRotation::isValidKey('ROTATION_HOURLY'))->toBeTrue()
        ->and(LogRotation::isValidKey('INVALID'))->toBeFalse();
});

it('converts rotation keys to enum cases', function () {
    expect(LogRotation::fromKey('ROTATION_DAILY'))->toBe(LogRotation::ROTATION_DAILY)
        ->and(LogRotation::fromKey('ROTATION_HOURLY'))->toBe(LogRotation::ROTATION_HOURLY)
        ->and(LogRotation::fromKey('INVALID'))->toBeNull();
});
