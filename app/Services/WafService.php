<?php

namespace App\Services;

use App\Models\AttackLog;
use App\Models\QuarantinedIp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WafService
{
    const MODE_MONITOR = 'monitor';
    const MODE_SANITIZE = 'sanitize';
    const MODE_QUARANTINE = 'quarantine';

    /**
     * Common SQL Injection Signatures with Severity Weight
     */
    protected array $signatures = [
        ['pattern' => '/\b(UNION\s+ALL\s+SELECT|UNION\s+SELECT)\b/i', 'name' => 'UNION-based Exfiltration', 'weight' => 35],
        ['pattern' => '/\b(DROP\s+TABLE|DROP\s+DATABASE|TRUNCATE\s+TABLE)\b/i', 'name' => 'Destructive DDL Command', 'weight' => 40],
        ['pattern' => '/\b(INSERT\s+INTO|DELETE\s+FROM|UPDATE\s+\w+\s+SET)\b/i', 'name' => 'Unauthorized DML Manipulation', 'weight' => 30],
        ['pattern' => '/(\'|\")\s*OR\s*(\'|\")?1(\'|\")?\s*=\s*(\'|\")?1/i', 'name' => 'Classic Tautology (OR 1=1)', 'weight' => 30],
        ['pattern' => '/\b(SLEEP\s*\(|BENCHMARK\s*\(|WAITFOR\s+DELAY|pg_sleep)/i', 'name' => 'Time-Based Blind Probe', 'weight' => 30],
        ['pattern' => '/(--|#|\/\*)/', 'name' => 'SQL Comment Query Truncation', 'weight' => 20],
        ['pattern' => '/;\s*(SELECT|INSERT|UPDATE|DELETE|DROP)/i', 'name' => 'Stacked Query Injection', 'weight' => 35],
        ['pattern' => '/\b(INFORMATION_SCHEMA|sqlite_master|sys\.databases)\b/i', 'name' => 'Metadata Schema Discovery', 'weight' => 25],
        ['pattern' => '/\b(CHAR|CONCAT|HEX|UNHEX|SUBSTR|MID)\s*\(/i', 'name' => 'SQL Function Obfuscation', 'weight' => 15],
    ];

    /**
     * Get Current WAF Mode from Cache (defaults to 'monitor')
     */
    public function getMode(): string
    {
        return Cache::get('waf_mode', self::MODE_MONITOR);
    }

    /**
     * Set WAF Mode
     */
    public function setMode(string $mode): void
    {
        Cache::put('waf_mode', $mode, now()->addDays(30));
    }

    /**
     * Inspect incoming string payload and compute threat score (0 - 100)
     */
    public function analyzePayload(string $payload): array
    {
        $threatScore = 0;
        $matchedRules = [];

        foreach ($this->signatures as $sig) {
            if (preg_match($sig['pattern'], $payload)) {
                $threatScore += $sig['weight'];
                $matchedRules[] = [
                    'rule' => $sig['name'],
                    'pattern' => $sig['pattern'],
                    'weight' => $sig['weight'],
                ];
            }
        }

        // Entropy / special character density check
        $specialCharsCount = preg_match_all('/[\'\"\-\;\#\/\*]/', $payload);
        if ($specialCharsCount >= 3) {
            $threatScore += 10;
        }

        $threatScore = min(100, $threatScore);

        $severity = match (true) {
            $threatScore >= 60 => 'Critical',
            $threatScore >= 35 => 'High',
            $threatScore >= 15 => 'Medium',
            default => 'Low',
        };

        return [
            'score' => $threatScore,
            'severity' => $severity,
            'is_malicious' => $threatScore >= 20,
            'matches' => $matchedRules,
        ];
    }

    /**
     * Sanitize text by stripping SQL injection syntax
     */
    public function sanitizeText(string $input): string
    {
        $sanitized = $input;
        foreach ($this->signatures as $sig) {
            $sanitized = preg_replace($sig['pattern'], '', $sanitized);
        }
        return trim($sanitized);
    }

    /**
     * Check if IP is currently quarantined
     */
    public function isQuarantined(string $ip): bool
    {
        $record = QuarantinedIp::where('ip_address', $ip)->first();
        if (!$record) {
            return false;
        }

        if ($record->expires_at && $record->expires_at->isPast()) {
            $record->delete();
            return false;
        }

        return true;
    }

    /**
     * Quarantine an IP address
     */
    public function quarantineIp(string $ip, string $reason, ?string $payload = null, int $durationMinutes = 30): QuarantinedIp
    {
        $record = QuarantinedIp::firstOrNew(['ip_address' => $ip]);
        $record->reason = $reason;
        $record->last_payload = substr($payload ?? '', 0, 250);
        $record->violation_count = ($record->violation_count ?? 0) + 1;
        $record->expires_at = now()->addMinutes($durationMinutes);
        $record->save();

        return $record;
    }

    /**
     * Release / Unblock IP
     */
    public function releaseIp(string $ip): bool
    {
        return (bool) QuarantinedIp::where('ip_address', $ip)->delete();
    }
}
