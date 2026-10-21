@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="card shadow-lg border-danger mx-auto" style="max-width: 650px; border-radius: 16px;">
        <div class="card-body p-5">
            <div class="text-danger mb-3">
                <i class="bi bi-shield-slash fs-1" style="font-size: 4rem !important;"></i>
            </div>
            <h2 class="fw-bold text-danger mb-2">403 - Request Blocked by WAF</h2>
            <p class="text-secondary mb-4">
                The Web Application Firewall has identified high-risk SQL Injection patterns originating from your IP address (<code class="text-danger font-monospace">{{ $ip }}</code>) while accessing <code class="font-monospace">{{ $path }}</code>.
            </p>
            <div class="alert alert-warning text-start small mb-4">
                <strong>Why was this blocked?</strong><br>
                The WAF is currently operating in <strong>Quarantine Mode</strong>. Dangerous SQL syntax patterns (such as Tautology, UNION queries, or Stacked DDL statements) automatically trigger protective quarantine to preserve database integrity.
            </div>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('waf.index') }}" class="btn btn-primary px-4">
                    <i class="bi bi-shield-check me-1"></i> Open WAF Guard Console
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
