<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SubscriptionNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // optional
        if ($value === null || $value === '') {
            return;
        }

        // Strip non-digit characters
        $digits = preg_replace('/\D+/', '', (string) $value);

        // Must be exactly 10 digits
        if (strlen($digits) !== 10) {
            $fail('The subscription number must be 10 digits.');

            return;
        }

        $base = substr($digits, 0, 8);  // first 8
        $check = substr($digits, 8, 2); // last 2

        $mod = ((int) $base) % 97;

        if ((int) $check !== $mod) {
            $fail('The subscription number checksum is invalid.');
        }
    }
}
