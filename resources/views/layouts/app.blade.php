<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Laravel SQL Injection Demo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .card-header {
            font-weight: 600;
        }

        .table th {
            vertical-align: middle;
            text-align: center;
        }

        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 13px;
        }

        pre {
            background: #212529;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            overflow: auto;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            color: #0d6efd;
        }

        .page-item.active .page-link {
            background: #0d6efd;
            border-color: #0d6efd;
        }

        .stat-card {
            transition: .3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        footer {
            margin-top: 50px;
            color: #6c757d;
            font-size: 14px;
        }
    </style>

</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">

        <div class="container">

            <a class="navbar-brand"
                href="{{ route('home') }}">

                Laravel SQL Injection Demo

            </a>

            <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse"
                id="navbarNav">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('home') }}">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-danger"
                            href="{{ route('unsafe.raw') }}">
                            Unsafe Raw SQL
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-danger"
                            href="{{ route('unsafe.whereRaw') }}">
                            Unsafe whereRaw
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-success"
                            href="{{ route('safe.parameterized') }}">
                            Safe Parameterized
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-success"
                            href="{{ route('safe.eloquent') }}">
                            Safe Eloquent
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-success"
                            href="{{ route('safe.queryBuilder') }}">
                            Safe Query Builder
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-warning"
                            href="{{ route('attack.logs') }}">
                            Attack Logs
                        </a>
                    </li>

                </ul>

            </div>

        </div>

    </nav>

    <div class="container py-4">

        @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button class="btn-close"
                data-bs-dismiss="alert"></button>

        </div>

        @endif

        @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button class="btn-close"
                data-bs-dismiss="alert"></button>

        </div>

        @endif

        @yield('content')

    </div>

    <footer class="text-center py-4">

        <hr>

        <p>

            Laravel 12 SQL Injection Prevention Demo

        </p>

        <small>

            Features Included:
            Search • Pagination • Statistics • CSV Export • Safe Queries • SQL Injection Demonstration

        </small>

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>