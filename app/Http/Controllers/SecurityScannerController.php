<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SecurityScannerController extends Controller
{
    /**
     * Display Security Scanner Dashboard
     */
    public function index()
    {
        return view('scanner.index');
    }

    /**
     * Run Static Codebase Analysis for SQL Injection Patterns
     */
    public function scan()
    {
        $directoriesToScan = [
            app_path('Http/Controllers'),
            app_path('Models'),
            base_path('routes'),
        ];

        $results = [];
        $totalFiles = 0;

        $patterns = [
            [
                'regex' => '/DB::select\s*\(\s*["\'].*?\$[a-zA-Z_]/i',
                'title' => 'Unsafe DB::select() with Variable Concatenation',
                'severity' => 'Critical',
                'description' => 'Direct variable interpolation inside raw SQL select query string without prepared statement parameter binding.',
                'recommendation' => 'Replace with DB::select("SELECT ... WHERE col = ?", [$value]) or use Eloquent ORM.',
            ],
            [
                'regex' => '/->whereRaw\s*\(\s*["\'].*?\$[a-zA-Z_]/i',
                'title' => 'Unsafe whereRaw() without Bindings',
                'severity' => 'Critical',
                'description' => 'User or variable input directly injected into whereRaw string without passing an array of parameter bindings.',
                'recommendation' => 'Pass bindings as the second argument: ->whereRaw("col = ?", [$val]) or use ->where("col", $val).',
            ],
            [
                'regex' => '/DB::raw\s*\(\s*["\'].*?\$[a-zA-Z_]/i',
                'title' => 'Raw DB::raw() Expression with Dynamic Variables',
                'severity' => 'High',
                'description' => 'Dynamic variable concatenated inside DB::raw() expression.',
                'recommendation' => 'Avoid injecting user variables into DB::raw(). Use parameter binding or sanitize with strict column whitelisting.',
            ],
            [
                'regex' => '/->orderByRaw\s*\(\s*["\'].*?\$[a-zA-Z_]/i',
                'title' => 'Unsafe orderByRaw() Clause',
                'severity' => 'Medium',
                'description' => 'Sort direction or column passed dynamically into orderByRaw without strict whitelist validation.',
                'recommendation' => 'Validate column and direction against an allowed array: in_array($dir, ["asc", "desc"]).',
            ],
        ];

        foreach ($directoriesToScan as $dir) {
            if (!File::isDirectory($dir)) continue;

            $files = File::allFiles($dir);
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') continue;
                $totalFiles++;

                $filePath = $file->getRealPath();
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath);
                $lines = file($filePath);

                foreach ($lines as $lineNum => $lineContent) {
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern['regex'], $lineContent)) {
                            // Extract surrounding snippet (1 line before, target line, 1 line after)
                            $prevLine = isset($lines[$lineNum - 1]) ? trim($lines[$lineNum - 1]) : '';
                            $nextLine = isset($lines[$lineNum + 1]) ? trim($lines[$lineNum + 1]) : '';

                            $results[] = [
                                'file' => $relativePath,
                                'line' => $lineNum + 1,
                                'code' => trim($lineContent),
                                'snippet' => [
                                    $lineNum => $prevLine,
                                    $lineNum + 1 => trim($lineContent),
                                    $lineNum + 2 => $nextLine,
                                ],
                                'title' => $pattern['title'],
                                'severity' => $pattern['severity'],
                                'description' => $pattern['description'],
                                'recommendation' => $pattern['recommendation'],
                            ];
                        }
                    }
                }
            }
        }

        $criticalCount = count(array_filter($results, fn($r) => $r['severity'] === 'Critical'));
        $highCount = count(array_filter($results, fn($r) => $r['severity'] === 'High'));
        $mediumCount = count(array_filter($results, fn($r) => $r['severity'] === 'Medium'));

        return response()->json([
            'success' => true,
            'total_files_scanned' => $totalFiles,
            'total_issues' => count($results),
            'critical_issues' => $criticalCount,
            'high_issues' => $highCount,
            'medium_issues' => $mediumCount,
            'issues' => $results,
        ]);
    }
}
