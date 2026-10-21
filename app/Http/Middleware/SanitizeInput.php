<?php

namespace App\Http\Middleware;

use App\Models\AttackLog;
use App\Services\WafService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $waf = app(WafService::class);
        $ip = $request->ip() ?: '127.0.0.1';
        $currentPath = $request->path();

        // Always allow WAF control panel and static assets
        if (str_starts_with($currentPath, 'waf-guard') || str_starts_with($currentPath, 'up')) {
            return $next($request);
        }

        // Check if IP is currently quarantined
        if ($waf->isQuarantined($ip)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'WAF_QUARANTINE_ACTIVE',
                    'message' => 'Your IP address is currently quarantined by the Web Application Firewall due to repeated SQL injection violations.',
                ], 403);
            }

            return response()->view('waf.blocked', [
                'ip' => $ip,
                'path' => $currentPath,
            ], 403);
        }

        $mode = $waf->getMode();
        $hasMaliciousPayload = false;
        $highestAnalysis = null;

        foreach ($request->all() as $key => $value) {
            if (!is_string($value) || empty($value)) {
                continue;
            }

            $analysis = $waf->analyzePayload($value);

            if ($analysis['is_malicious']) {
                $hasMaliciousPayload = true;
                $highestAnalysis = $analysis;

                // Log into AttackLog
                AttackLog::create([
                    'ip_address' => $ip,
                    'route' => $currentPath,
                    'payload' => substr($value, 0, 500),
                    'pattern' => implode(', ', array_column($analysis['matches'], 'rule')),
                    'method' => $request->method(),
                ]);

                Log::warning('WAF SQL Injection Detected', [
                    'ip' => $ip,
                    'path' => $currentPath,
                    'score' => $analysis['score'],
                    'payload' => $value,
                ]);

                // If mode is quarantine and score is critical, quarantine immediately
                if ($mode === WafService::MODE_QUARANTINE && $analysis['score'] >= 50) {
                    $waf->quarantineIp($ip, 'High-threat SQL Injection signature detected: ' . ($analysis['matches'][0]['rule'] ?? 'Generic'), $value, 30);
                    
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => 'WAF_QUARANTINED',
                            'message' => 'Malicious SQL payload detected. Your IP has been temporarily quarantined.',
                        ], 403);
                    }

                    return response()->view('waf.blocked', [
                        'ip' => $ip,
                        'path' => $currentPath,
                    ], 403);
                }

                // If mode is sanitize, clean the input in place
                if ($mode === WafService::MODE_SANITIZE) {
                    $cleanValue = $waf->sanitizeText($value);
                    $request->merge([$key => $cleanValue]);
                }
            }
        }

        return $next($request);
    }
}
