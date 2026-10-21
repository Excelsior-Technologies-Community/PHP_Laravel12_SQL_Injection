@extends('layouts.app')

@section('title', 'Live SQL Query AST & Prepared Statement Visualizer')

@section('content')
<!-- Header Banner -->
<div class="card p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-pill">AST Tokenizer</span>
                <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1 rounded-pill">PDO Driver Level</span>
            </div>
            <h2 class="fw-bold mb-1">🔬 Live SQL Query AST & Prepared Statement Visualizer</h2>
            <p class="text-secondary mb-0">
                Inspect how the database engine and PHP Data Objects (PDO) lexically analyze queries. Discover why string concatenation mutates the compiler's Abstract Syntax Tree while prepared statements treat values strictly as byte literals.
            </p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Home
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Input Section -->
    <div class="col-lg-4">
        <div class="card p-4 border-0 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-input-cursor-text text-primary"></i> Query Input Inspector
            </h5>

            <!-- Presets -->
            <label class="form-label small text-muted fw-semibold">Sample Search Values:</label>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="setInputValue('Alice')">
                    Normal Name
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setInputValue('\' OR \'1\'=\'1')">
                    Tautology Exploit
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="setInputValue('Robert\'); DROP TABLE Students;--')">
                    Bobby Tables (DDL)
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark" onclick="setInputValue('\' UNION SELECT password FROM users --')">
                    UNION Exfiltration
                </button>
            </div>

            <div class="mb-3">
                <label for="inspectInput" class="form-label fw-semibold">User Search Input ($search)</label>
                <input type="text" id="inspectInput" class="form-control font-mono" value="' OR '1'='1" placeholder="Enter input...">
            </div>

            <button id="inspectBtn" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm" onclick="inspectQuery()">
                <i class="bi bi-search me-1"></i> Inspect AST & Token Streams
            </button>
        </div>

        <!-- Token Legend Card -->
        <div class="card p-4 border-0 shadow-sm">
            <h6 class="fw-bold mb-3 text-dark">Lexer Token Legend</h6>
            <div class="d-flex flex-column gap-2 small">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge badge-keyword px-2 py-1">KEYWORD</span>
                    <span class="text-muted">Reserved SQL commands (SELECT, WHERE, OR)</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge badge-identifier px-2 py-1">IDENTIFIER</span>
                    <span class="text-muted">Table or column names (users, email)</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge badge-literal px-2 py-1">LITERAL</span>
                    <span class="text-muted">Quoted string or numerical constants</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge badge-placeholder px-2 py-1">PLACEHOLDER</span>
                    <span class="text-muted">PDO prepared placeholder (?)</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge badge-comment px-2 py-1">COMMENT</span>
                    <span class="text-muted">Comment syntax truncating query (-- , #)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Visualization Results -->
    <div class="col-lg-8">
        <!-- Explanatory Box -->
        <div id="alertBox" class="alert alert-danger shadow-sm mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1" id="alertTitle">AST Syntax Alteration Detected!</h6>
                    <p class="mb-0 small" id="alertDesc">Analyzing...</p>
                </div>
            </div>
        </div>

        <!-- 1. Vulnerable AST Breakdown -->
        <div class="card p-4 border-0 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-x-circle-fill me-1"></i> Vulnerable Token Stream (Concatenated String)</h6>
                <span class="badge bg-danger">Compromised AST</span>
            </div>
            <pre id="rawSqlText" class="p-2 mb-3 small font-mono">--</pre>

            <label class="small text-muted fw-bold mb-2">LEXICAL PARSER TOKENS:</label>
            <div id="rawTokensBox" class="d-flex flex-wrap gap-2 p-3 bg-light rounded border">
                <!-- Tokens rendered here -->
            </div>
            <small class="text-danger mt-2 d-block">
                ⚠️ Notice how the input has created independent <code>KEYWORD</code> and <code>OPERATOR</code> tokens, rewriting the parser logic!
            </small>
        </div>

        <!-- 2. Secure Prepared Statement & PDO Bindings -->
        <div class="card p-4 border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0 text-success"><i class="bi bi-check-circle-fill me-1"></i> Secure Prepared Statement (PDO Driver Level)</h6>
                <span class="badge bg-success">Isolated AST</span>
            </div>
            <pre id="preparedSqlText" class="p-2 mb-3 small font-mono">--</pre>

            <label class="small text-muted fw-bold mb-2">PREPARED STATEMENT TOKENS:</label>
            <div id="preparedTokensBox" class="d-flex flex-wrap gap-2 p-3 bg-light rounded border mb-3">
                <!-- Prepared Tokens -->
            </div>

            <!-- PDO Bindings Inspector Table -->
            <label class="small text-muted fw-bold mb-2">PDO BOUND PARAMETERS TABLE (Delivered Post-Compilation):</label>
            <div class="table-responsive border rounded">
                <table class="table table-sm table-hover font-mono small mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Param Index</th>
                            <th>Target Column</th>
                            <th>Bound Literal Value</th>
                            <th>PDO Data Type</th>
                            <th>Parser Status</th>
                        </tr>
                    </thead>
                    <tbody id="bindingsTableBody">
                        <!-- Rows -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function setInputValue(val) {
        document.getElementById('inspectInput').value = val;
        inspectQuery();
    }

    async function inspectQuery() {
        const input = document.getElementById('inspectInput').value;
        const btn = document.getElementById('inspectBtn');
        btn.disabled = true;

        try {
            const res = await fetch("{{ route('visualizer.inspect') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ input: input })
            });

            const data = await res.json();

            // SQL texts
            document.getElementById('rawSqlText').innerText = data.raw_sql;
            document.getElementById('preparedSqlText').innerText = data.prepared_sql;

            // Alert Box
            const alertBox = document.getElementById('alertBox');
            if (data.syntax_compromised) {
                alertBox.className = "alert alert-danger shadow-sm mb-4";
                document.getElementById('alertTitle').innerText = "AST Syntax Alteration Detected!";
                document.getElementById('alertDesc').innerText = data.explanation.vulnerable_risk;
            } else {
                alertBox.className = "alert alert-success shadow-sm mb-4";
                document.getElementById('alertTitle').innerText = "Benign Payload (No Injected Keywords)";
                document.getElementById('alertDesc').innerText = "This input does not break out of SQL string literals, but prepared statements remain critical to prevent any single quote from doing so.";
            }

            // Raw Tokens
            const rawBox = document.getElementById('rawTokensBox');
            rawBox.innerHTML = '';
            data.raw_tokens.forEach(t => {
                const badge = document.createElement('span');
                badge.className = `badge ${t.badge} p-2 font-mono`;
                badge.innerText = `${t.token} (${t.type})`;
                rawBox.appendChild(badge);
            });

            // Prepared Tokens
            const prepBox = document.getElementById('preparedTokensBox');
            prepBox.innerHTML = '';
            data.prepared_tokens.forEach(t => {
                const badge = document.createElement('span');
                badge.className = `badge ${t.badge} p-2 font-mono`;
                badge.innerText = `${t.token} (${t.type})`;
                prepBox.appendChild(badge);
            });

            // Bindings Table
            const tbody = document.getElementById('bindingsTableBody');
            tbody.innerHTML = '';
            data.bound_parameters.forEach(b => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><span class="badge bg-secondary font-mono">${b.placeholder} [${b.index}]</span></td>
                    <td class="text-primary fw-bold">${b.target_column}</td>
                    <td class="text-success text-break">${escapeHtml(b.value)}</td>
                    <td><code>${b.pdo_type}</code></td>
                    <td><span class="badge bg-success">Escaped Byte Literal</span></td>
                `;
                tbody.appendChild(tr);
            });

        } catch (e) {
            alert('Inspection failed: ' + e.message);
        } finally {
            btn.disabled = false;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    document.addEventListener('DOMContentLoaded', () => {
        inspectQuery();
    });
</script>
@endsection
