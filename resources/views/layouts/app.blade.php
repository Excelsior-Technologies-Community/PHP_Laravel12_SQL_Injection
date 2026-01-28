<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel SQL Injection Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .vulnerable { background-color: #f8d7da; }
        .safe { background-color: #d1ecf1; }
        .card { margin-bottom: 20px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">SQL Injection Demo</a>
                <div class="navbar-nav">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                    <a class="nav-link" href="{{ route('unsafe.raw') }}">Unsafe Raw SQL</a>
                    <a class="nav-link" href="{{ route('unsafe.whereRaw') }}">Unsafe whereRaw</a>
                    <a class="nav-link" href="{{ route('safe.parameterized') }}">Safe Parameterized</a>
                    <a class="nav-link" href="{{ route('safe.eloquent') }}">Safe Eloquent</a>
                    <a class="nav-link" href="{{ route('safe.queryBuilder') }}">Safe Query Builder</a>
                </div>
            </div>
        </nav>
        
        <div class="row">
            <div class="col-12">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
            </div>
        </div>
        
        @yield('content')
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>