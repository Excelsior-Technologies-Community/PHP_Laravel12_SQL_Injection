@extends('layouts.app')

@section('title', 'Vulnerable Authentication & Login Bypass Arena')

@section('content')
<!-- Header Banner -->
<div class="card p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-3 py-1 rounded-pill">Authentication Vulnerability</span>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-pill">Credential Hijacking Arena</span>
            </div>
            <h2 class="fw-bold mb-1">🔐 Vulnerable Authentication & Login Bypass Arena</h2>
            <p class="text-secondary mb-0">
                Experience how classical SQL injection allows attackers to bypass password verification and hijack administrative accounts using comment syntax and tautologies, and contrast it with Laravel's built-in secure authentication.
            </p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Home
        </a>
    </div>
</div>

<!-- Current Session Status -->
@if($currentUser)
<div class="card p-3 mb-4 border-0 shadow-sm bg-white border-start border-4 border-success">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <span class="badge {{ $currentUser->is_admin ? 'bg-danger' : 'bg-primary' }} mb-1">
                {{ $currentUser->is_admin ? 'ADMINISTRATOR SESSION' : 'REGULAR USER' }}
            </span>
            <h5 class="fw-bold mb-0">Authenticated as: <span class="text-primary">{{ $currentUser->name }}</span> ({{ $currentUser->email }})</h5>
            <small class="text-muted">User ID: #{{ $currentUser->id }} &bull; Account Created: {{ $currentUser->created_at->format('M d, Y') }}</small>
        </div>
        <form action="{{ route('auth.bypass.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-right me-1"></i> Terminate Session (Logout)
            </button>
        </form>
    </div>
</div>
@endif

<!-- Arena Tabs -->
<div class="row g-4">
    <!-- Vulnerable Login Box -->
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100 border-top border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-unlock-fill me-1"></i> Vulnerable Login Form</h5>
                <span class="badge bg-danger">Unsafe Concatenation</span>
            </div>
            <p class="small text-muted mb-3">
                Executes: <code>SELECT * FROM users WHERE email = '$email' AND password = '$password'</code> without parameter binding.
            </p>

            <!-- Quick Bypass Presets -->
            <label class="form-label small text-muted fw-semibold">Quick 1-Click Exploit Presets:</label>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setUnsafeCredentials('admin@example.com\' --', '')">
                    🎯 Target Admin via Comment (--)
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setUnsafeCredentials('\' OR \'1\'=\'1\' --', '')">
                    🔓 First Record Tautology
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setUnsafeCredentials('admin@example.com', 'password')">
                    Normal Admin Password
                </button>
            </div>

            <form action="{{ route('auth.bypass.login-unsafe') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email or Injected Payload</label>
                    <input type="text" id="unsafeEmail" name="email" class="form-control font-mono" value="admin@example.com' --" required>
                    <small class="text-muted">Notice how the trailing <code class="text-danger">--</code> comments out the password condition.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password (Leave Empty to Test Bypass!)</label>
                    <input type="password" id="unsafePassword" name="password" class="form-control font-mono" placeholder="No password needed with injection!">
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
                    <i class="bi bi-shield-slash me-1"></i> Attempt Unsafe Login
                </button>
            </form>

            <div class="mt-4 p-3 bg-light rounded border small">
                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-lightbulb text-warning me-1"></i> How this exploit works:</h6>
                <div class="font-mono text-secondary my-1">
                    SELECT * FROM users WHERE email = '<span class="text-danger fw-bold">admin@example.com' --</span>' AND password = '...'
                </div>
                <p class="text-muted mb-0" style="font-size: 11px;">
                    The SQL parser sees the comment marker <code>--</code> and completely disregards everything after it. The query succeeds based purely on the email match, authenticating the attacker instantly!
                </p>
            </div>
        </div>
    </div>

    <!-- Secure Login Box -->
    <div class="col-lg-6">
        <div class="card p-4 border-0 shadow-sm h-100 border-top border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-success"><i class="bi bi-shield-lock-fill me-1"></i> Secure Laravel Authentication</h5>
                <span class="badge bg-success">Hash Verified & Parameterized</span>
            </div>
            <p class="small text-muted mb-3">
                Executes parameterized lookup: <code>User::where('email', ?)->first()</code> and verifies with <code>Hash::check()</code>.
            </p>

            <!-- Quick Preset -->
            <label class="form-label small text-muted fw-semibold">Test Same Exploit on Secure Form:</label>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="setSafeCredentials('admin@example.com\' --', '')">
                    Test Same Injected Payload
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setSafeCredentials('admin@example.com', 'password')">
                    Legitimate Admin Credentials
                </button>
            </div>

            <form action="{{ route('auth.bypass.login-safe') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="text" id="safeEmail" name="email" class="form-control font-mono" value="admin@example.com' --" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" id="safePassword" name="password" class="form-control font-mono" placeholder="Enter password...">
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Attempt Secure Login
                </button>
            </form>

            <div class="mt-4 p-3 bg-light rounded border small">
                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-check2-circle text-success me-1"></i> Why this is 100% Secure:</h6>
                <p class="text-muted mb-0" style="font-size: 11px;">
                    1. The database searches for an exact literal email matching <code class="text-secondary">"admin@example.com' --"</code>.<br>
                    2. Even if found, Laravel compares the password against the one-way Argon2/Bcrypt hash using timing-safe comparisons. The bypass is rendered completely inert.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function setUnsafeCredentials(email, password) {
        document.getElementById('unsafeEmail').value = email;
        document.getElementById('unsafePassword').value = password;
    }

    function setSafeCredentials(email, password) {
        document.getElementById('safeEmail').value = email;
        document.getElementById('safePassword').value = password;
    }
</script>
@endsection
