@extends('layouts.app')

@section('title', 'Real-Time WAF & IP Quarantine Guard')

@section('content')
<!-- Header Banner -->
<div class="card p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill">Active WAF Layer</span>
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-1 rounded-pill">Heuristic Threat Scoring</span>
            </div>
            <h2 class="fw-bold mb-1">🚨 Real-Time WAF & IP Quarantine Guard</h2>
            <p class="text-secondary mb-0">
                Inspects incoming traffic for high-risk SQL injection patterns. Switch between <strong class="text-primary">Monitor</strong>, <strong class="text-warning">Sanitize</strong>, and <strong class="text-danger">Quarantine</strong> modes to protect endpoints in real-time.
            </p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Home
        </a>
    </div>
</div>

<!-- Mode Switcher & Stats -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-toggles text-primary"></i> WAF Operating Mode
            </h5>
            <p class="small text-muted mb-3">Choose how the firewall responds when malicious SQL injection attempts are detected across all routes:</p>

            <form action="{{ route('waf.mode') }}" method="POST">
                @csrf
                <div class="d-flex flex-column gap-3 mb-4">
                    <label class="p-3 border rounded-3 cursor-pointer d-flex align-items-start gap-3 {{ $currentMode === 'monitor' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                        <input type="radio" name="mode" value="monitor" class="form-check-input mt-1" {{ $currentMode === 'monitor' ? 'checked' : '' }} onchange="this.form.submit()">
                        <div>
                            <span class="fw-bold text-dark d-block">1. MONITOR ONLY (Passive Detection)</span>
                            <small class="text-muted">Logs attack payloads to the database, sends warnings to system logs, and lets the request pass through.</small>
                        </div>
                    </label>

                    <label class="p-3 border rounded-3 cursor-pointer d-flex align-items-start gap-3 {{ $currentMode === 'sanitize' ? 'border-warning bg-warning bg-opacity-10' : '' }}">
                        <input type="radio" name="mode" value="sanitize" class="form-check-input mt-1" {{ $currentMode === 'sanitize' ? 'checked' : '' }} onchange="this.form.submit()">
                        <div>
                            <span class="fw-bold text-dark d-block">2. SANITIZE & NEUTRALIZE (In-Flight Scrubbing)</span>
                            <small class="text-muted">Automatically strips SQL keywords, UNION tokens, and comments in-memory before controller invocation.</small>
                        </div>
                    </label>

                    <label class="p-3 border rounded-3 cursor-pointer d-flex align-items-start gap-3 {{ $currentMode === 'quarantine' ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                        <input type="radio" name="mode" value="quarantine" class="form-check-input mt-1" {{ $currentMode === 'quarantine' ? 'checked' : '' }} onchange="this.form.submit()">
                        <div>
                            <span class="fw-bold text-dark d-block">3. BLOCK & QUARANTINE (Active Defense)</span>
                            <small class="text-muted">Immediately blocks the client with an HTTP 403 Forbidden screen and adds their IP to the quarantine blacklist for 30 minutes.</small>
                        </div>
                    </label>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Test Console -->
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-bug-fill text-danger"></i> Live WAF Payload Tester
            </h5>
            <div class="mb-3">
                <label for="testPayloadInput" class="form-label small fw-semibold">Test Any Injected String Against Active WAF Engine:</label>
                <div class="input-group">
                    <input type="text" id="testPayloadInput" class="form-control font-mono" value="admin' UNION SELECT password FROM users --">
                    <button class="btn btn-dark" type="button" onclick="testWafPayload()">Analyze</button>
                </div>
            </div>

            <!-- Analysis Output -->
            <div id="wafTestResult" class="p-3 bg-light rounded border small">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark">Threat Score:</span>
                    <span id="testScoreBadge" class="badge bg-danger fs-6">0 / 100</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Simulated WAF Action:</span>
                    <strong id="testActionText" class="text-primary font-mono ms-1">ALLOW</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Matched Signatures:</span>
                    <div id="testMatchedRules" class="mt-1 d-flex flex-wrap gap-1">
                        <span class="text-muted small">None</span>
                    </div>
                </div>
                <div>
                    <span class="text-muted">Sanitized Preview:</span>
                    <code id="testSanitizedOutput" class="d-block font-mono bg-white p-2 rounded border mt-1">--</code>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quarantined IPs Table -->
<div class="card p-4 border-0 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-shield-x text-danger"></i> Quarantined IP Addresses
            <span class="badge bg-danger rounded-pill">{{ $totalQuarantined }}</span>
        </h5>

        <!-- Manual Block Modal Trigger -->
        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#manualBlockModal">
            <i class="bi bi-plus-circle me-1"></i> Add Manual IP Ban
        </button>
    </div>

    @if($quarantinedIps->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover align-middle font-mono small mb-0">
            <thead class="table-light">
                <tr>
                    <th>IP Address</th>
                    <th>Reason / Trigger</th>
                    <th>Last Attack Payload</th>
                    <th>Violations</th>
                    <th>Expires At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quarantinedIps as $q)
                <tr>
                    <td class="fw-bold text-danger">{{ $q->ip_address }}</td>
                    <td class="text-secondary">{{ $q->reason }}</td>
                    <td class="text-truncate" style="max-width: 250px;">
                        <code class="text-danger">{{ $q->last_payload ?: 'Manual Ban' }}</code>
                    </td>
                    <td><span class="badge bg-secondary">{{ $q->violation_count }}</span></td>
                    <td class="text-muted">{{ $q->expires_at ? $q->expires_at->diffForHumans() : 'Permanent' }}</td>
                    <td class="text-end">
                        <form action="{{ route('waf.unblock', $q->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-unlock-fill me-1"></i> Release
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $quarantinedIps->links() }}
    </div>
    @else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-shield-check text-success fs-1 d-block mb-2"></i>
        <h6>No IP addresses are currently quarantined.</h6>
        <small>When WAF is in <strong>Quarantine Mode</strong>, any client triggering high-severity SQLi is listed here automatically.</small>
    </div>
    @endif
</div>

<!-- Manual Block Modal -->
<div class="modal fade" id="manualBlockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('waf.manual-block') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Manually Quarantine IP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target IP Address</label>
                        <input type="text" name="ip_address" class="form-control font-mono" placeholder="192.168.1.100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason for Quarantine</label>
                        <input type="text" name="reason" class="form-control" value="Manual administrator threat containment">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ban Duration (Minutes)</label>
                        <input type="number" name="duration" class="form-control" value="60" min="5" max="1440">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Quarantine</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function testWafPayload() {
        const payload = document.getElementById('testPayloadInput').value;
        try {
            const res = await fetch("{{ route('waf.test') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ payload: payload })
            });
            const data = await res.json();

            // Threat score
            const badge = document.getElementById('testScoreBadge');
            badge.innerText = `${data.threat_score} / 100 (${data.severity})`;
            badge.className = `badge fs-6 ${data.threat_score >= 50 ? 'bg-danger' : (data.threat_score >= 20 ? 'bg-warning text-dark' : 'bg-success')}`;

            // Simulated action
            document.getElementById('testActionText').innerText = data.simulated_action;

            // Matched rules
            const rulesBox = document.getElementById('testMatchedRules');
            rulesBox.innerHTML = '';
            if (data.matched_rules && data.matched_rules.length > 0) {
                data.matched_rules.forEach(r => {
                    const sp = document.createElement('span');
                    sp.className = 'badge bg-danger p-1';
                    sp.innerText = r.rule;
                    rulesBox.appendChild(sp);
                });
            } else {
                rulesBox.innerHTML = '<span class="text-success small">Clean (No signatures triggered)</span>';
            }

            // Sanitized preview
            document.getElementById('testSanitizedOutput').innerText = data.sanitized_output || '(Empty string)';

        } catch (e) {
            console.error(e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        testWafPayload();
    });
</script>
@endsection
