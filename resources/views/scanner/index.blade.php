@extends('layouts.app')

@section('title', 'Automated Codebase SQLi Static Security Scanner')

@section('content')
<!-- Header Banner -->
<div class="card p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-pill">Static Code Analysis (SAST)</span>
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-1 rounded-pill">Vulnerability Auditing</span>
            </div>
            <h2 class="fw-bold mb-1">🔍 Automated Codebase SQLi Static Security Scanner</h2>
            <p class="text-secondary mb-0">
                Audits project PHP source code in real-time. Automatically identifies insecure database invocations (such as unparameterized <code>whereRaw()</code>, unescaped <code>DB::raw()</code>, or concatenated <code>DB::select()</code>) and suggests safe refactoring fixes.
            </p>
        </div>
        <div>
            <button id="startScanBtn" class="btn btn-primary" onclick="runStaticScan()">
                <i class="bi bi-shield-check me-1"></i> Run Security Code Audit
            </button>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4 text-center">
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted text-uppercase fw-bold">Files Scanned</small>
            <h3 id="statFiles" class="fw-bold text-dark my-1">0</h3>
            <span class="small text-muted">Controllers, Models & Routes</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted text-uppercase fw-bold">Critical Vulnerabilities</small>
            <h3 id="statCritical" class="fw-bold text-danger my-1">0</h3>
            <span class="small text-danger">Unparameterized Raw SQL</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted text-uppercase fw-bold">High/Medium Risks</small>
            <h3 id="statHigh" class="fw-bold text-warning my-1">0</h3>
            <span class="small text-warning">Dynamic Raw Expressions</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted text-uppercase fw-bold">Codebase Health</small>
            <h3 id="statHealth" class="fw-bold text-success my-1">--</h3>
            <span class="small text-muted">Security Audit Rating</span>
        </div>
    </div>
</div>

<!-- Findings List -->
<div class="card p-4 border-0 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Audit Findings & Remediation Advice</h5>
        <span id="issuesCountBadge" class="badge bg-secondary">0 issues detected</span>
    </div>

    <div id="findingsContainer" class="d-flex flex-column gap-3">
        <div class="text-center py-5 text-muted">
            <i class="bi bi-search fs-1 d-block mb-2 text-primary"></i>
            <h6>Ready to Audit Project Codebase</h6>
            <small>Click <strong>"Run Security Code Audit"</strong> to scan controllers and routes.</small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function runStaticScan() {
        const btn = document.getElementById('startScanBtn');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Auditing Files...`;

        try {
            const res = await fetch("{{ route('scanner.scan') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await res.json();

            // Stats
            document.getElementById('statFiles').innerText = data.total_files_scanned;
            document.getElementById('statCritical').innerText = data.critical_issues;
            document.getElementById('statHigh').innerText = data.high_issues + data.medium_issues;

            const health = data.critical_issues > 0 ? "VULNERABLE (DEMO)" : "SECURE";
            document.getElementById('statHealth').innerText = health;
            document.getElementById('statHealth').className = `fw-bold my-1 ${data.critical_issues > 0 ? 'text-danger' : 'text-success'}`;
            document.getElementById('issuesCountBadge').innerText = `${data.total_issues} issues found`;

            // Render Findings
            const container = document.getElementById('findingsContainer');
            container.innerHTML = '';

            if (data.issues && data.issues.length > 0) {
                data.issues.forEach(issue => {
                    const card = document.createElement('div');
                    card.className = 'border rounded-3 p-3 bg-white shadow-sm';
                    card.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div>
                                <span class="badge ${issue.severity === 'Critical' ? 'bg-danger' : 'bg-warning text-dark'} font-mono me-2">${issue.severity}</span>
                                <strong class="text-dark">${escapeHtml(issue.title)}</strong>
                            </div>
                            <span class="badge bg-secondary font-mono">${escapeHtml(issue.file)} : Line ${issue.line}</span>
                        </div>
                        <p class="small text-muted mb-2">${escapeHtml(issue.description)}</p>
                        
                        <div class="p-2 bg-dark rounded font-mono text-danger small mb-2">
                            <code>${escapeHtml(issue.code)}</code>
                        </div>

                        <div class="p-2 bg-success bg-opacity-10 rounded border border-success-subtle small">
                            <strong class="text-success d-block mb-1"><i class="bi bi-lightbulb-fill me-1"></i> Recommended Safe Fix:</strong>
                            <span class="text-dark font-mono">${escapeHtml(issue.recommendation)}</span>
                        </div>
                    `;
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                        <h6 class="text-success fw-bold">Clean Codebase Audit</h6>
                        <small class="text-muted">No raw query interpolations detected in the scanned controllers.</small>
                    </div>
                `;
            }

        } catch (e) {
            alert('Scan error: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-arrow-repeat me-1"></i> Re-Scan Codebase`;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    document.addEventListener('DOMContentLoaded', () => {
        runStaticScan();
    });
</script>
@endsection
