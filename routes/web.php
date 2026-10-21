<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VulnerableUserController;
use App\Http\Controllers\AttackLogController;
use App\Http\Controllers\ExploitPlaygroundController;
use App\Http\Controllers\QueryVisualizerController;
use App\Http\Controllers\WafGuardController;
use App\Http\Controllers\AuthBypassController;
use App\Http\Controllers\BlindSqliLabController;
use App\Http\Controllers\SecurityScannerController;
use App\Http\Controllers\ThreatIntelligenceController;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');


/*
|--------------------------------------------------------------------------
| Vulnerable Examples
|--------------------------------------------------------------------------
*/

Route::get('/unsafe-raw', [
    VulnerableUserController::class,
    'unsafeSearch'
])->name('unsafe.raw');

Route::get('/unsafe-whereraw', [
    VulnerableUserController::class,
    'unsafeWhereRaw'
])->name('unsafe.whereRaw');


/*
|--------------------------------------------------------------------------
| Safe Examples
|--------------------------------------------------------------------------
*/

Route::get('/safe-parameterized', [
    VulnerableUserController::class,
    'safeParameterized'
])->name('safe.parameterized');

Route::get('/safe-eloquent', [
    VulnerableUserController::class,
    'safeEloquent'
])->name('safe.eloquent');

Route::get('/safe-querybuilder', [
    VulnerableUserController::class,
    'safeQueryBuilder'
])->name('safe.queryBuilder');


/*
|--------------------------------------------------------------------------
| Export CSV Routes
|--------------------------------------------------------------------------
*/

Route::get('/unsafe-raw/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->unsafeSearch(request());
})->name('unsafe.raw.export');


Route::get('/unsafe-whereraw/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->unsafeWhereRaw(request());
})->name('unsafe.whereRaw.export');


Route::get('/safe-parameterized/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->safeParameterized(request());
})->name('safe.parameterized.export');


Route::get('/safe-eloquent/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->safeEloquent(request());
})->name('safe.eloquent.export');


Route::get('/safe-querybuilder/export', function () {
    request()->merge(['export' => 1]);
    return app(VulnerableUserController::class)->safeQueryBuilder(request());
})->name('safe.queryBuilder.export');


Route::get('/attack-logs', [
    AttackLogController::class,
    'index'
])->name('attack.logs');

Route::delete('/attack-logs/{id}', [
    AttackLogController::class,
    'destroy'
])->name('attack.logs.delete');

Route::delete('/attack-logs', [
    AttackLogController::class,
    'clear'
])->name('attack.logs.clear');

/*
|--------------------------------------------------------------------------
| Advanced Security Labs & Defense Modules
|--------------------------------------------------------------------------
*/

// 1. Exploit Playground & 1-Click Attack Simulator
Route::get('/exploit-playground', [ExploitPlaygroundController::class, 'index'])->name('playground.index');
Route::post('/exploit-playground/run', [ExploitPlaygroundController::class, 'run'])->name('playground.run');

// 2. Live SQL Query AST & Prepared Statement Visualizer
Route::get('/query-visualizer', [QueryVisualizerController::class, 'index'])->name('visualizer.index');
Route::post('/query-visualizer/inspect', [QueryVisualizerController::class, 'inspect'])->name('visualizer.inspect');

// 3. Real-Time WAF (Web Application Firewall) & IP Quarantine Guard
Route::get('/waf-guard', [WafGuardController::class, 'index'])->name('waf.index');
Route::post('/waf-guard/mode', [WafGuardController::class, 'updateMode'])->name('waf.mode');
Route::delete('/waf-guard/unblock/{id}', [WafGuardController::class, 'unblock'])->name('waf.unblock');
Route::post('/waf-guard/manual-block', [WafGuardController::class, 'manualBlock'])->name('waf.manual-block');
Route::post('/waf-guard/test', [WafGuardController::class, 'testPayload'])->name('waf.test');

// 4. Vulnerable Authentication & Login Bypass Arena
Route::get('/auth-bypass-arena', [AuthBypassController::class, 'index'])->name('auth.bypass.index');
Route::post('/auth-bypass-arena/login-unsafe', [AuthBypassController::class, 'loginUnsafe'])->name('auth.bypass.login-unsafe');
Route::post('/auth-bypass-arena/login-safe', [AuthBypassController::class, 'loginSafe'])->name('auth.bypass.login-safe');
Route::post('/auth-bypass-arena/logout', [AuthBypassController::class, 'logout'])->name('auth.bypass.logout');

// 5. Blind SQL Injection & Timing Analysis Lab
Route::get('/blind-sqli-lab', [BlindSqliLabController::class, 'index'])->name('blind.index');
Route::post('/blind-sqli-lab/timing', [BlindSqliLabController::class, 'timing'])->name('blind.timing');
Route::post('/blind-sqli-lab/extract-step', [BlindSqliLabController::class, 'extractStep'])->name('blind.extract-step');

// 6. Automated Codebase SQLi Static Security Scanner
Route::get('/security-scanner', [SecurityScannerController::class, 'index'])->name('scanner.index');
Route::post('/security-scanner/scan', [SecurityScannerController::class, 'scan'])->name('scanner.scan');

// 7. Threat Intelligence Analytics & OWASP Compliance Dashboard
Route::get('/threat-intelligence', [ThreatIntelligenceController::class, 'index'])->name('threat.index');
Route::get('/threat-intelligence/export', [ThreatIntelligenceController::class, 'exportReport'])->name('threat.export');
