<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts strings, numbers and booleans — rejects arrays and objects.
 */
class ScalarValue implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== null && ! is_scalar($value)) {
            $fail('validation.scalar')->translate();
        }
    }
}
