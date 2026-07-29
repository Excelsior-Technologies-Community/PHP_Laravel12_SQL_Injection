<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\AttackLog;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $patterns = [

            '/DROP/i',

            '/DELETE/i',

            '/INSERT/i',

            '/UPDATE/i',

            '/ALTER/i',

            '/UNION/i',

            '/SELECT/i',

            '/--/',

            '/;/',

            '/OR\s+1=1/i',

            '/\'\s*OR\s*\'1\'=\'1/i'

        ];

        foreach ($request->all() as $value) {

            if (!is_string($value)) {
                continue;
            }

            foreach ($patterns as $pattern) {

                if (preg_match($pattern, $value)) {

                    Log::warning('SQL Injection Attempt', [

                        'payload' => $value,

                        'ip' => $request->ip(),

                        'route' => $request->path()

                    ]);

                    AttackLog::create([

                        'ip_address' => $request->ip(),

                        'route' => $request->path(),

                        'payload' => $value,

                        'pattern' => $pattern,

                        'method' => $request->method()

                    ]);

                    break;
                }
            }
        }

        return $next($request);
    }
}