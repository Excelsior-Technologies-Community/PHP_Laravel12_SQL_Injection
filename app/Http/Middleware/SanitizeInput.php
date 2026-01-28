<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            // Remove potentially dangerous SQL keywords (additional layer of security)
            $dangerousPatterns = [
                '/\bDROP\b/i',
                '/\bDELETE\b/i',
                '/\bINSERT\b/i',
                '/\bUPDATE\b/i',
                '/\bALTER\b/i',
                '/\bEXEC(\s|\()+.*/i',
                '/\bUNION\b/i',
                '/--/',
                '/;/'
            ];
            
            // For demonstration, just log suspicious patterns
            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    Log::warning('Suspicious input detected', [
                        'input' => $value,
                        'pattern' => $pattern
                    ]);
                }
            }
        });
        
        // Note: We're not modifying the input, just logging
        // In production, you might want to sanitize or reject
        return $next($request);
    }
}