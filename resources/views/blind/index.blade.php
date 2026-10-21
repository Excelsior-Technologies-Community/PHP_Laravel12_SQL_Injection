@extends('layouts.app')

@section('title', 'Blind SQL Injection & Timing Analysis Lab')

@section('content')
<!-- Header Banner -->
<div class="card p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-warning bg-opacity-10 text-dark fw-bold px-3 py-1 rounded-pill">Time-Based Blind Probe</span>
                <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1 rounded-pill">Boolean True/False Inference</span>
            </div>
            <h2 class="fw-bold mb-1">⏱️ Blind SQL Injection & Timing Analysis Lab</h2>
            <p class="text-secondary mb-0">
                Understand how attackers extract sensitive records when the application reveals <strong>no database output and zero SQL errors</strong> on screen. Learn how response delays and boolean page status are leveraged to exfiltrate secrets.
            </p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Home
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Lab 1: Time-Based Blind SQLi -->
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-hourglass-split text-warning"></i> Lab 1: Time-Based Blind Probe (SLEEP)
            </h5>
            <p class="small text-muted mb-3">
                In time-based blind injection, attackers inject conditional time-delay commands (e.g. <code>SLEEP(3)</code> or <code>WAITFOR DELAY</code>). If a condition is TRUE, the server hangs before replying.
            </p>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Select Artificial Time Delay (Seconds):</label>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1" onclick="runTiming('baseline', 0)">
                        ⚡ Baseline Query (0s delay)
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1" onclick="runTiming('injected', 1)">
                        ⏱️ Injected SLEEP(1s)
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1" onclick="runTiming('injected', 2)">
                        ⏱️ Injected SLEEP(2s)
                    </button>
                </div>
            </div>

            <!-- Execution Result Box -->
            <div id="timingResultCard" class="p-3 bg-light rounded border small mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Query Response Latency:</span>
                    <span id="timingLatencyBadge" class="fs-5 fw-bold text-dark font-mono">0.00 ms</span>
                </div>
                <div class="progress mb-2" style="height: 10px;">
                    <div id="timingProgressBar" class="progress-bar bg-success" style="width: 5%;"></div>
                </div>
                <label class="text-muted small fw-bold">EVALUATED SQL STATEMENT:</label>
                <pre id="timingSqlDisplay" class="font-mono small p-2 mb-2 bg-dark text-info">Click a test button above to run.</pre>
                <div id="timingExplanation" class="text-muted small">--</div>
            </div>
        </div>
    </div>

    <!-- Lab 2: Boolean-Based Character Extractor -->
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-puzzle-fill text-info"></i> Lab 2: Boolean Character Extraction Simulation
            </h5>
            <p class="small text-muted mb-3">
                Watch how an automated script checks character-by-character using <code>SUBSTR(column, position, 1) = 'character'</code> to extract a secret administrator username without ever seeing query output.
            </p>

            <!-- Target info -->
            <div class="alert alert-secondary py-2 px-3 small font-mono mb-3">
                Target Record: <strong>Admin User (is_admin = 1)</strong><br>
                Subquery: <code>SUBSTR(name, [pos], 1) == '[char]'</code>
            </div>

            <div class="d-flex gap-2 mb-3">
                <button id="startExtractionBtn" class="btn btn-primary btn-sm px-3" onclick="startAutomatedExtraction()">
                    <i class="bi bi-play-fill me-1"></i> Start Automated Byte-by-Byte Extraction
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="resetExtraction()">Reset</button>
            </div>

            <!-- Extracted String Visualizer -->
            <label class="form-label small fw-semibold">Extracted String Buffer:</label>
            <div class="d-flex gap-1 mb-3" id="characterSlotsBox">
                <!-- Character slots will appear here -->
            </div>

            <div class="p-3 bg-light rounded border small">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted">Current Probe:</span>
                    <span id="extractorProbeStatus" class="font-mono text-primary fw-bold">Idle</span>
                </div>
                <div class="font-mono small text-secondary" id="extractorSqlDisplay">Ready to inspect condition responses.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function runTiming(type, delay) {
        const badge = document.getElementById('timingLatencyBadge');
        const prog = document.getElementById('timingProgressBar');
        badge.innerText = "Running...";
        prog.style.width = "100%";
        prog.className = "progress-bar progress-bar-striped progress-bar-animated bg-warning";

        try {
            const res = await fetch("{{ route('blind.timing') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ type: type, delay: delay })
            });
            const data = await res.json();

            badge.innerText = data.elapsed_ms + " ms";
            document.getElementById('timingSqlDisplay').innerText = data.sql;
            document.getElementById('timingExplanation').innerText = data.explanation;

            if (data.elapsed_ms > 800) {
                prog.className = "progress-bar bg-danger";
                prog.style.width = "95%";
            } else {
                prog.className = "progress-bar bg-success";
                prog.style.width = "15%";
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Automated Boolean Extractor
    let isExtracting = false;
    const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz 0123456789";

    function resetExtraction() {
        isExtracting = false;
        document.getElementById('startExtractionBtn').disabled = false;
        document.getElementById('extractorProbeStatus').innerText = "Reset";
        document.getElementById('extractorSqlDisplay').innerText = "Ready to inspect condition responses.";
        initSlots(10);
    }

    function initSlots(length) {
        const box = document.getElementById('characterSlotsBox');
        box.innerHTML = '';
        for (let i = 0; i < length; i++) {
            const span = document.createElement('div');
            span.id = `slot-${i + 1}`;
            span.className = 'border text-center font-mono fw-bold rounded bg-white text-muted';
            span.style.width = '36px';
            span.style.height = '42px';
            span.style.lineHeight = '40px';
            span.innerText = '?';
            box.appendChild(span);
        }
    }

    async function startAutomatedExtraction() {
        if (isExtracting) return;
        isExtracting = true;
        const btn = document.getElementById('startExtractionBtn');
        btn.disabled = true;

        initSlots(10);

        for (let pos = 1; pos <= 10; pos++) {
            if (!isExtracting) break;

            const slot = document.getElementById(`slot-${pos}`);
            if (slot) {
                slot.className = 'border text-center font-mono fw-bold rounded bg-warning text-dark';
            }

            let found = false;

            for (let i = 0; i < alphabet.length; i++) {
                if (!isExtracting) break;
                const char = alphabet[i];

                document.getElementById('extractorProbeStatus').innerText = `Testing Position ${pos} == '${char}'`;

                const res = await fetch("{{ route('blind.extract-step') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ position: pos, char: char })
                });

                const data = await res.json();
                document.getElementById('extractorSqlDisplay').innerText = `${data.sql_condition} => ${data.server_response}`;

                if (data.is_match) {
                    found = true;
                    if (slot) {
                        slot.className = 'border text-center font-mono fw-bold rounded bg-success text-white shadow-sm';
                        slot.innerText = char;
                    }
                    break;
                }
            }

            if (!found && slot) {
                slot.className = 'border text-center font-mono fw-bold rounded bg-light text-muted';
                slot.innerText = ' ';
            }
        }

        document.getElementById('extractorProbeStatus').innerText = "✅ Extraction Finished!";
        btn.disabled = false;
        isExtracting = false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        initSlots(10);
    });
</script>
@endsection
