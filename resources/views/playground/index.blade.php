@extends('layouts.app')

@section('title', 'Interactive Exploit Playground & 1-Click Attack Simulator')

@section('content')
<!-- Header Banner -->
<div class="card p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-1 rounded-pill">Penetration Sandbox</span>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill">Safe vs Unsafe Comparator</span>
            </div>
            <h2 class="fw-bold mb-1">🛡️ Interactive Exploit Playground & Attack Simulator</h2>
            <p class="text-secondary mb-0">
                Test industry-standard SQL injection attack vectors with 1-click presets. Compare how an unsafe concatenated query gets exploited in real-time while the safe PDO prepared statement neutralizes the payload.
            </p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Home
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Attack Presets & Custom Payload -->
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge-fill text-warning"></i> 1-Click Attack Vectors
            </h5>
            <div class="d-flex flex-column gap-2 mb-4">
                @foreach($presets as $preset)
                <button type="button" class="btn btn-outline-dark text-start p-3 rounded-3 preset-btn" onclick="applyPreset({{ json_encode($preset) }})">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark">{{ $preset['name'] }}</span>
                        <span class="badge bg-danger font-mono" style="font-size: 10px;">{{ $preset['severity'] }}</span>
                    </div>
                    <code class="text-danger small font-mono d-block text-truncate mb-1">{{ $preset['payload'] }}</code>
                    <small class="text-muted d-block" style="font-size: 11px;">{{ $preset['description'] }}</small>
                </button>
                @endforeach
            </div>

            <!-- Custom Payload Input -->
            <h6 class="fw-bold mb-2">Custom Attack Payload</h6>
            <div class="mb-3">
                <textarea id="attackPayload" class="form-control font-mono" rows="3" placeholder="Enter SQL payload...">' OR '1'='1</textarea>
                <small class="text-muted">Injected into both the unsafe and safe search endpoints simultaneously.</small>
            </div>

            <button id="runSimulatorBtn" class="btn btn-danger w-100 py-2 fw-semibold shadow-sm" onclick="runSimulation()">
                <i class="bi bi-play-circle-fill me-1"></i> Fire Simulation Vectors
            </button>
        </div>
    </div>

    <!-- Right Column: Split Screen Comparison Results -->
    <div class="col-lg-8">
        <!-- Live Status Bar -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card p-3 border-danger shadow-sm text-center bg-danger bg-opacity-10">
                    <small class="text-danger fw-bold text-uppercase">Vulnerable Route Status</small>
                    <h3 id="vulnStatusText" class="fw-bold text-danger my-1">Standby</h3>
                    <span id="vulnMetaText" class="small text-muted font-mono">0 rows exfiltrated &bull; 0.00 ms</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 border-success shadow-sm text-center bg-success bg-opacity-10">
                    <small class="text-success fw-bold text-uppercase">Secure PDO Route Status</small>
                    <h3 id="safeStatusText" class="fw-bold text-success my-1">Standby</h3>
                    <span id="safeMetaText" class="small text-muted font-mono">0 rows returned &bull; 0.00 ms</span>
                </div>
            </div>
        </div>

        <!-- Split Screen Details -->
        <div class="row g-3">
            <!-- Vulnerable Box -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-danger">❌ Vulnerable (Raw String Concatenation)</span>
                        <span id="vulnBadge" class="badge bg-secondary font-mono">--</span>
                    </div>
                    <label class="small text-muted fw-bold">RAW SQL GENERATED & EXECUTED:</label>
                    <pre id="vulnSqlDisplay" class="font-mono small p-2 rounded mb-3" style="max-height: 120px;">--</pre>

                    <label class="small text-muted fw-bold">DATABASE RESPONSE / EXFILTRATED DATA:</label>
                    <div id="vulnResultsBox" class="table-responsive border rounded bg-light p-2" style="min-height: 250px; max-height: 380px; overflow-y: auto;">
                        <p class="text-muted text-center py-5 small mb-0">Select an attack vector or fire simulation to view results.</p>
                    </div>
                </div>
            </div>

            <!-- Safe Box -->
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-success">✅ Safe (Prepared Statement Binding)</span>
                        <span id="safeBadge" class="badge bg-secondary font-mono">--</span>
                    </div>
                    <label class="small text-muted fw-bold">PARAMETRIC SQL & ISOLATED BINDINGS:</label>
                    <pre id="safeSqlDisplay" class="font-mono small p-2 rounded mb-3" style="max-height: 120px;">--</pre>

                    <label class="small text-muted fw-bold">DATABASE RESPONSE (NEUTRALIZED):</label>
                    <div id="safeResultsBox" class="table-responsive border rounded bg-light p-2" style="min-height: 250px; max-height: 380px; overflow-y: auto;">
                        <p class="text-muted text-center py-5 small mb-0">Select an attack vector or fire simulation to view results.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function applyPreset(preset) {
        document.getElementById('attackPayload').value = preset.payload;
        runSimulation();
    }

    async function runSimulation() {
        const payload = document.getElementById('attackPayload').value;
        const btn = document.getElementById('runSimulatorBtn');
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Simulating Exploits...`;

        try {
            const res = await fetch("{{ route('playground.run') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ payload: payload })
            });

            const data = await res.json();

            // Vulnerable Box UI
            document.getElementById('vulnSqlDisplay').innerText = data.vulnerable.sql;
            document.getElementById('vulnMetaText').innerText = `${data.vulnerable.rows_count} rows exfiltrated • ${data.vulnerable.time_ms} ms`;
            
            if (data.vulnerable.is_exploited) {
                document.getElementById('vulnStatusText').innerText = "🔥 EXPLOIT SUCCEEDED";
                document.getElementById('vulnStatusText').className = "fw-bold text-danger my-1";
                document.getElementById('vulnBadge').className = "badge bg-danger font-mono";
                document.getElementById('vulnBadge').innerText = "BYPASS / LEAK DETECTED";
            } else if (data.vulnerable.error) {
                document.getElementById('vulnStatusText').innerText = "⚠️ QUERY THREW ERROR";
                document.getElementById('vulnStatusText').className = "fw-bold text-warning my-1";
                document.getElementById('vulnBadge').className = "badge bg-warning text-dark font-mono";
                document.getElementById('vulnBadge').innerText = "SQL SYNTAX ERROR";
            } else {
                document.getElementById('vulnStatusText').innerText = "ZERO MATCHES";
                document.getElementById('vulnStatusText').className = "fw-bold text-secondary my-1";
                document.getElementById('vulnBadge').className = "badge bg-secondary font-mono";
                document.getElementById('vulnBadge').innerText = "CLEAN";
            }

            // Render Vulnerable rows or error
            const vulnBox = document.getElementById('vulnResultsBox');
            if (data.vulnerable.error) {
                vulnBox.innerHTML = `<div class="alert alert-danger font-mono small mb-0"><i class="bi bi-bug me-1"></i> ${escapeHtml(data.vulnerable.error)}</div>`;
            } else if (data.vulnerable.rows && data.vulnerable.rows.length > 0) {
                let html = `<table class="table table-sm table-striped font-mono small mb-0"><thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Email</th><th>Admin</th></tr></thead><tbody>`;
                data.vulnerable.rows.forEach(r => {
                    html += `<tr><td>${r.id}</td><td>${escapeHtml(r.name)}</td><td>${escapeHtml(r.email)}</td><td><span class="badge ${r.is_admin ? 'bg-danger' : 'bg-secondary'}">${r.is_admin ? 'Yes' : 'No'}</span></td></tr>`;
                });
                html += `</tbody></table>`;
                if (data.vulnerable.has_more) {
                    html += `<div class="text-center small text-muted py-1 bg-white border-top">+ more rows exfiltrated via SQLi</div>`;
                }
                vulnBox.innerHTML = html;
            } else {
                vulnBox.innerHTML = `<p class="text-muted text-center py-4 small mb-0">No records returned.</p>`;
            }

            // Safe Box UI
            document.getElementById('safeSqlDisplay').innerText = data.safe.sql + "\nBindings: " + JSON.stringify(data.safe.bindings);
            document.getElementById('safeMetaText').innerText = `${data.safe.rows_count} rows returned • ${data.safe.time_ms} ms`;
            document.getElementById('safeStatusText').innerText = "🛡️ ATTACK NEUTRALIZED";
            document.getElementById('safeBadge').className = "badge bg-success font-mono";
            document.getElementById('safeBadge').innerText = "SAFE LITERAL";

            const safeBox = document.getElementById('safeResultsBox');
            if (data.safe.rows && data.safe.rows.length > 0) {
                let html = `<table class="table table-sm table-striped font-mono small mb-0"><thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Email</th><th>Admin</th></tr></thead><tbody>`;
                data.safe.rows.forEach(r => {
                    html += `<tr><td>${r.id}</td><td>${escapeHtml(r.name)}</td><td>${escapeHtml(r.email)}</td><td><span class="badge ${r.is_admin ? 'bg-danger' : 'bg-secondary'}">${r.is_admin ? 'Yes' : 'No'}</span></td></tr>`;
                });
                html += `</tbody></table>`;
                safeBox.innerHTML = html;
            } else {
                safeBox.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-shield-check text-success fs-1 d-block mb-2"></i>
                        <span class="text-success fw-bold d-block">Zero Records Injected</span>
                        <small class="text-muted">The SQL engine treated the entire payload as a literal string argument.</small>
                    </div>
                `;
            }

        } catch (e) {
            alert('Simulation error: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-play-circle-fill me-1"></i> Fire Simulation Vectors`;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Auto-run default once
    document.addEventListener('DOMContentLoaded', () => {
        runSimulation();
    });
</script>
@endsection
