<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSqlInjection implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $dangerousPatterns = [
            '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|EXEC)\b/i',
            '/--/',
            '/;/',
            '/\/\*.*\*\//',
            '/\bUNION\b.*\bSELECT\b/i',
            '/\bOR\b\s+\b1\b\s*=\s*\b1\b/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail("The $attribute contains potentially dangerous content.");
            }
        }
    }
}