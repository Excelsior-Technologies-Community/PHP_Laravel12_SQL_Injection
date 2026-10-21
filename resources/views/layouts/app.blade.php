<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Laravel 12 SQL Injection Defense & Security Lab')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-accent: #2563eb;
            --danger-accent: #dc2626;
            --success-accent: #16a34a;
            --warning-accent: #f59e0b;
        }

        body {
            background: #f1f5f9;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .navbar-custom {
            background: #0f172a;
            border-bottom: 1px solid #1e293b;
            padding: 10px 24px;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.15rem;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link-item {
            color: #94a3b8 !important;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 6px 12px !important;
            border-radius: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link-item:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-link-item.active {
            color: #ffffff !important;
            background: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 14px 20px;
        }

        pre {
            background: #0f172a;
            color: #38bdf8;
            padding: 16px;
            border-radius: 10px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            overflow-x: auto;
        }

        .badge-keyword { background: #3b82f6; color: #fff; }
        .badge-identifier { background: #64748b; color: #fff; }
        .badge-literal { background: #10b981; color: #fff; }
        .badge-operator { background: #f59e0b; color: #fff; }
        .badge-placeholder { background: #ec4899; color: #fff; }
        .badge-comment { background: #ef4444; color: #fff; }

        footer {
            margin-top: auto;
            background: #0f172a;
            color: #94a3b8;
            padding: 24px 0;
            font-size: 13px;
            border-top: 1px solid #1e293b;
        }
    </style>
    @yield('styles')
</head>

<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-xl navbar-dark navbar-custom shadow-sm sticky-top">
        <div class="container-fluid px-3">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-shield-lock-fill text-danger fs-4"></i>
                <span>Laravel 12 <span class="text-primary">SQLi Defense Lab</span></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-center gap-1 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house-door"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('playground.*') ? 'active' : '' }}" href="{{ route('playground.index') }}">
                            <i class="bi bi-lightning-charge-fill text-warning"></i> Playground
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('visualizer.*') ? 'active' : '' }}" href="{{ route('visualizer.index') }}">
                            <i class="bi bi-cpu text-info"></i> AST Visualizer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('waf.*') ? 'active' : '' }}" href="{{ route('waf.index') }}">
                            <i class="bi bi-shield-shaded text-success"></i> WAF Guard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('auth.bypass.*') ? 'active' : '' }}" href="{{ route('auth.bypass.index') }}">
                            <i class="bi bi-key-fill text-danger"></i> Auth Arena
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('blind.*') ? 'active' : '' }}" href="{{ route('blind.index') }}">
                            <i class="bi bi-stopwatch-fill text-warning"></i> Blind SQLi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('scanner.*') ? 'active' : '' }}" href="{{ route('scanner.index') }}">
                            <i class="bi bi-search text-primary"></i> Code Scanner
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-item {{ request()->routeIs('threat.*') ? 'active' : '' }}" href="{{ route('threat.index') }}">
                            <i class="bi bi-bar-chart-line-fill text-info"></i> Threat Intel
                        </a>
                    </li>

                    <!-- Dropdown for Classic Demos -->
                    <li class="nav-item dropdown ms-xl-2">
                        <a class="nav-link-item dropdown-toggle btn btn-sm btn-outline-secondary text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-folder2-open me-1"></i> Classic Queries
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header text-danger">❌ Vulnerable Queries</h6></li>
                            <li><a class="dropdown-item" href="{{ route('unsafe.raw') }}"><i class="bi bi-bug me-2 text-danger"></i>Unsafe Raw SQL</a></li>
                            <li><a class="dropdown-item" href="{{ route('unsafe.whereRaw') }}"><i class="bi bi-bug me-2 text-danger"></i>Unsafe whereRaw()</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-success">✅ Secure Queries</h6></li>
                            <li><a class="dropdown-item" href="{{ route('safe.parameterized') }}"><i class="bi bi-check2-circle me-2 text-success"></i>Safe Parameterized</a></li>
                            <li><a class="dropdown-item" href="{{ route('safe.eloquent') }}"><i class="bi bi-check2-circle me-2 text-success"></i>Safe Eloquent ORM</a></li>
                            <li><a class="dropdown-item" href="{{ route('safe.queryBuilder') }}"><i class="bi bi-check2-circle me-2 text-success"></i>Safe Query Builder</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('attack.logs') }}"><i class="bi bi-journal-text me-2 text-warning"></i>Attack History Logs</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container Body -->
    <div class="container-fluid px-lg-5 py-4 flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="fw-semibold">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-1 fw-bold text-white">Laravel 12 SQL Injection Defense & Penetration Testing Laboratory</p>
            <p class="mb-0 text-secondary small">
                Educational Security Demonstration &bull; PDO Prepared Statements &bull; WAF Layer &bull; AST Query Inspection &bull; OWASP A03:2021
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')

</body>
</html>
