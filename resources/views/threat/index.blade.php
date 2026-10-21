@extends('layouts.app')

@section('title', 'Threat Intelligence & OWASP Compliance Dashboard')

@section('content')
<!-- Header Banner -->
<div class="card p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1 rounded-pill">Security Metrics</span>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill">OWASP A03:2021 Alignment</span>
            </div>
            <h2 class="fw-bold mb-1">📊 Threat Intelligence & OWASP Compliance Dashboard</h2>
            <p class="text-secondary mb-0">
                Aggregated threat analytics, attack pattern distribution, top targeted routes, and comprehensive OWASP Top 10 Injection compliance checklist.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('threat.export') }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Threat Logs (CSV)
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Home
            </a>
        </div>
    </div>
</div>

<!-- High Level Metrics -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 border-0 shadow-sm text-center">
            <small class="text-muted text-uppercase fw-bold">Total Injections Intercepted</small>
            <h2 class="fw-bold text-danger my-1">{{ $totalAttacks }}</h2>
            <span class="small text-muted">All AttackLog entries</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 border-0 shadow-sm text-center">
            <small class="text-muted text-uppercase fw-bold">Quarantined Threats</small>
            <h2 class="fw-bold text-warning my-1">{{ $totalQuarantined }}</h2>
            <span class="small text-muted">Blocked IP addresses</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 border-0 shadow-sm text-center">
            <small class="text-muted text-uppercase fw-bold">Active WAF Layer Mode</small>
            <h2 class="fw-bold text-primary my-1 text-uppercase">{{ $currentMode }}</h2>
            <span class="small text-muted">Web Application Firewall</span>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card p-3 border-0 shadow-sm text-center">
            <small class="text-muted text-uppercase fw-bold">OWASP Security Score</small>
            <h2 class="fw-bold text-success my-1">{{ $score }}%</h2>
            <span class="small text-success">Injection Resilience</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Top Targeted Endpoints -->
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-crosshair text-danger"></i> Top Targeted Endpoints
            </h5>
            @if($topRoutes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover font-mono small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Endpoint Path</th>
                            <th class="text-end">Attempts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topRoutes as $r)
                        <tr>
                            <td class="text-primary font-monospace fw-bold">/{{ $r->route }}</td>
                            <td class="text-end"><span class="badge bg-danger">{{ $r->count }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4 text-muted small">No endpoint attacks recorded yet.</div>
            @endif
        </div>
    </div>

    <!-- Attack Pattern Distribution -->
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart-fill text-info"></i> Observed Attack Signatures
            </h5>
            @if($topPatterns->count() > 0)
            <div class="d-flex flex-column gap-2">
                @foreach($topPatterns as $p)
                <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border small">
                    <span class="text-dark font-mono text-truncate" style="max-width: 380px;">{{ $p->pattern }}</span>
                    <span class="badge bg-secondary">{{ $p->count }} hits</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4 text-muted small">No signature patterns logged yet.</div>
            @endif
        </div>
    </div>
</div>

<!-- OWASP Top 10 A03:2021 Checklist -->
<div class="card p-4 border-0 shadow-sm mb-4">
    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-shield-check text-success"></i> OWASP Top 10 A03:2021 (Injection) Defense Checklist
    </h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Security Control / Best Practice</th>
                    <th>Mitigation Description</th>
                    <th class="text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($owaspChecklist as $item)
                <tr>
                    <td class="fw-bold text-dark">{{ $item['rule'] }}</td>
                    <td class="text-secondary">{{ $item['description'] }}</td>
                    <td class="text-end">
                        <span class="badge {{ $item['badge'] }} px-2 py-1">{{ $item['status'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
