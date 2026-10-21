<?php

namespace App\Http\Controllers;

use App\Models\AttackLog;
use App\Models\QuarantinedIp;
use App\Services\WafService;
use Illuminate\Http\Request;

class WafGuardController extends Controller
{
    /**
     * Display WAF & IP Quarantine Guard dashboard
     */
    public function index(WafService $waf)
    {
        $currentMode = $waf->getMode();
        $quarantinedIps = QuarantinedIp::latest()->paginate(10);
        $totalQuarantined = QuarantinedIp::count();
        $totalAttacks = AttackLog::count();
        $recentAttacks = AttackLog::latest()->take(6)->get();

        return view('waf.index', compact(
            'currentMode',
            'quarantinedIps',
            'totalQuarantined',
            'totalAttacks',
            'recentAttacks'
        ));
    }

    /**
     * Update active WAF operating mode
     */
    public function updateMode(Request $request, WafService $waf)
    {
        $mode = $request->input('mode', WafService::MODE_MONITOR);
        if (in_array($mode, [WafService::MODE_MONITOR, WafService::MODE_SANITIZE, WafService::MODE_QUARANTINE])) {
            $waf->setMode($mode);
            return back()->with('success', "WAF Operating Mode successfully updated to " . strtoupper($mode) . ".");
        }

        return back()->with('error', 'Invalid WAF mode selected.');
    }

    /**
     * Release an IP address from quarantine
     */
    public function unblock($id, WafService $waf)
    {
        $record = QuarantinedIp::findOrFail($id);
        $ip = $record->ip_address;
        $record->delete();

        return back()->with('success', "IP address {$ip} has been released from quarantine.");
    }

    /**
     * Manually quarantine an IP
     */
    public function manualBlock(Request $request, WafService $waf)
    {
        $ip = trim($request->input('ip_address', ''));
        $reason = trim($request->input('reason', 'Manual Administrator Block'));
        $duration = (int) $request->input('duration', 60);

        if (empty($ip)) {
            return back()->with('error', 'Please enter a valid IP address.');
        }

        $waf->quarantineIp($ip, $reason, 'Manual Administrative Block', $duration);

        return back()->with('success', "IP {$ip} successfully quarantined for {$duration} minutes.");
    }

    /**
     * Live WAF Test Inspector
     */
    public function testPayload(Request $request, WafService $waf)
    {
        $payload = trim($request->input('payload', ''));
        $analysis = $waf->analyzePayload($payload);
        $mode = $waf->getMode();

        $action = match ($mode) {
            WafService::MODE_QUARANTINE => ($analysis['score'] >= 50) ? 'BLOCK_AND_QUARANTINE_IP' : 'ALLOW_AND_LOG',
            WafService::MODE_SANITIZE => ($analysis['is_malicious']) ? 'SANITIZE_PAYLOAD' : 'ALLOW',
            default => ($analysis['is_malicious']) ? 'MONITOR_AND_LOG' : 'ALLOW',
        };

        $sanitizedVersion = $waf->sanitizeText($payload);

        return response()->json([
            'success' => true,
            'payload' => $payload,
            'mode' => $mode,
            'threat_score' => $analysis['score'],
            'severity' => $analysis['severity'],
            'is_malicious' => $analysis['is_malicious'],
            'matched_rules' => $analysis['matches'],
            'simulated_action' => $action,
            'sanitized_output' => $sanitizedVersion,
        ]);
    }
}
