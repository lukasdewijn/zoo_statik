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
        //optional
        if($value === null || $value === '') {
            return;
        }

        // enkel cijfers overhouden
        $digits = preg_replace('/\D+/', '', (string) $value);

        // exact 10 cijfers
        if(strlen($digits) !== 10) {
            $fail("The :attribute must be 10 digits.");
            return;
        }

        $base = substr($digits, 0, 8); // eerste 8
        $check = substr($digits, 8, 2); // laatste 2

        $mod = ((int) $base) % 97;

        if((int) $check !== $mod){
            $fail('The :attribute checksum is invalid.');
        }
    }
}
