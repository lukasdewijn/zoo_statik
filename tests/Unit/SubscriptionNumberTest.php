<?php

use App\Rules\SubscriptionNumber;

function passesRule(mixed $value): bool
{
    $passed = true;
    (new SubscriptionNumber)->validate('field', $value, function () use (&$passed) {
        $passed = false;
    });

    return $passed;
}

test('accepts null and empty string', function () {
    expect(passesRule(null))->toBeTrue();
    expect(passesRule(''))->toBeTrue();
});

test('accepts valid 10-digit subscription number with correct checksum', function () {
    // 12345678 % 97 = 3 -> check digits = 03
    expect(passesRule('1234567803'))->toBeTrue();
});

test('rejects number with wrong length', function () {
    expect(passesRule('12345'))->toBeFalse();
    expect(passesRule('123456789012'))->toBeFalse();
});

test('rejects number with invalid checksum', function () {
    // 12345678 % 97 = 60, so 99 is wrong
    expect(passesRule('1234567899'))->toBeFalse();
});

test('strips non-digit characters before validation', function () {
    // 12345678 % 97 = 3 -> check digits = 03
    expect(passesRule('1234-5678-03'))->toBeTrue();
});

test('rejects when stripped digits are not 10', function () {
    expect(passesRule('12-34'))->toBeFalse();
});
