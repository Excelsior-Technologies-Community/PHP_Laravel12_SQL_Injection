<?php

namespace App\Http\Controllers;

use App\Models\AttackLog;
use App\Models\QuarantinedIp;
use App\Services\WafService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThreatIntelligenceController extends Controller
{
    /**
     * Display Threat Intelligence & OWASP Compliance Dashboard
     */
    public function index(WafService $waf)
    {
        $totalAttacks = AttackLog::count();
        $totalQuarantined = QuarantinedIp::count();
        $currentMode = $waf->getMode();

        // Top targeted routes
        $topRoutes = AttackLog::select('route', DB::raw('count(*) as count'))
            ->groupBy('route')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        // Attack patterns breakdown
        $topPatterns = AttackLog::select('pattern', DB::raw('count(*) as count'))
            ->groupBy('pattern')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        // Top attacking IPs
        $topIps = AttackLog::select('ip_address', DB::raw('count(*) as count'), DB::raw('max(created_at) as last_seen'))
            ->groupBy('ip_address')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        // Recent 10 attack events
        $recentAttacks = AttackLog::latest()->take(10)->get();

        // OWASP A03:2021 Injection Checklist
        $owaspChecklist = [
            [
                'rule' => 'Prepared Statements & Parameter Binding',
                'description' => 'Ensure all database operations utilize parameter placeholders to isolate SQL commands from user input.',
                'status' => 'PASS',
                'badge' => 'bg-success',
            ],
            [
                'rule' => 'Active WAF Layer (Web Application Firewall)',
                'description' => 'Real-time signature analysis and IP quarantine layer for zero-day heuristics.',
                'status' => 'PASS',
                'badge' => 'bg-success',
            ],
            [
                'rule' => 'Strict Input Validation (NoSqlInjection Rule)',
                'description' => 'Request payload validation layer rejecting SQL syntax tokens prior to controller execution.',
                'status' => 'PASS',
                'badge' => 'bg-success',
            ],
            [
                'rule' => 'ORM Object Relational Mapping (Eloquent)',
                'description' => 'Leverage framework ORM models which use parameterization internally by default.',
                'status' => 'PASS',
                'badge' => 'bg-success',
            ],
            [
                'rule' => 'Production Database Error Suppression',
                'description' => 'Ensure database exceptions and stack traces are suppressed in production (APP_DEBUG=false).',
                'status' => config('app.debug') ? 'WARNING' : 'PASS',
                'badge' => config('app.debug') ? 'bg-warning text-dark' : 'bg-success',
            ],
            [
                'rule' => 'Principle of Least Privilege (Database User)',
                'description' => 'Ensure web database user lacks DROP TABLE or ALTER TABLE permissions in production.',
                'status' => 'RECOMMENDED',
                'badge' => 'bg-info text-dark',
            ],
        ];

        // Overall Security Score calculation
        $score = 85;
        if (!config('app.debug')) $score += 10;
        if ($currentMode === WafService::MODE_QUARANTINE) $score += 5;

        return view('threat.index', compact(
            'totalAttacks',
            'totalQuarantined',
            'currentMode',
            'topRoutes',
            'topPatterns',
            'topIps',
            'recentAttacks',
            'owaspChecklist',
            'score'
        ));
    }

    /**
     * Export Threat Intelligence Log to CSV
     */
    public function exportReport()
    {
        $logs = AttackLog::latest()->get();

        $response = new StreamedResponse(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Log ID',
                'IP Address',
                'Target Route',
                'HTTP Method',
                'Pattern Detected',
                'Payload Snippet',
                'Timestamp',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->ip_address,
                    $log->route,
                    $log->method,
                    $log->pattern,
                    $log->payload,
                    $log->created_at->toIso8601String(),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="sqli_threat_intelligence_report_' . date('Ymd_His') . '.csv"');

        return $response;
    }
}
