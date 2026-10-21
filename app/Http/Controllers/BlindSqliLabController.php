<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlindSqliLabController extends Controller
{
    /**
     * Display Blind SQL Injection & Timing Analysis Lab
     */
    public function index()
    {
        $admin = User::where('is_admin', true)->first();

        return view('blind.index', compact('admin'));
    }

    /**
     * Execute Time-Based Blind SQLi demonstration
     */
    public function timing(Request $request)
    {
        $payloadType = $request->input('type', 'baseline'); // 'baseline' or 'injected'
        $delaySeconds = min(5, max(1, (int) $request->input('delay', 2)));

        $start = microtime(true);
        $sql = "SELECT id, name FROM users WHERE email = 'admin@example.com'";

        if ($payloadType === 'injected') {
            // Simulate time-based sleep (safe cross-database simulation)
            $sql .= " /* Injected: SLEEP({$delaySeconds}) */";
            usleep($delaySeconds * 1000000);
        }

        $result = DB::select("SELECT id, name FROM users WHERE email = 'admin@example.com'");
        $elapsedMs = round((microtime(true) - $start) * 1000, 2);

        return response()->json([
            'success' => true,
            'type' => $payloadType,
            'sql' => $sql,
            'elapsed_ms' => $elapsedMs,
            'delay_seconds' => $payloadType === 'injected' ? $delaySeconds : 0,
            'result_count' => count($result),
            'explanation' => $payloadType === 'injected'
                ? "The database took ~{$elapsedMs}ms to respond because the injected SLEEP({$delaySeconds}) paused execution. When a condition is TRUE, the attacker observes this high latency, confirming their hypothesis even when no data is displayed!"
                : "The baseline query executed in {$elapsedMs}ms under normal operational conditions.",
        ]);
    }

    /**
     * Step-by-step Boolean-Based Blind extraction simulation
     */
    public function extractStep(Request $request)
    {
        $position = (int) $request->input('position', 1);
        $guessChar = (string) $request->input('char', 'A');

        // Target: Admin's username ('Admin User')
        $admin = User::where('is_admin', true)->first();
        $actualChar = $admin ? substr($admin->name, $position - 1, 1) : '';

        // Boolean condition query
        $conditionSql = "SELECT COUNT(*) as total FROM users WHERE is_admin = 1 AND SUBSTR(name, {$position}, 1) = '{$guessChar}'";
        
        $isMatch = ($guessChar === $actualChar);

        return response()->json([
            'success' => true,
            'position' => $position,
            'tested_char' => $guessChar,
            'sql_condition' => $conditionSql,
            'is_match' => $isMatch,
            'server_response' => $isMatch ? 'TRUE (Record found / Normal HTTP 200)' : 'FALSE (Zero records found)',
            'full_length' => $admin ? strlen($admin->name) : 10,
        ]);
    }
}
