@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body text-center">
            <h2 class="fw-bold text-primary">
                Laravel 12 SQL Injection Prevention Demo
            </h2>

            <p class="text-muted mb-0">
                Learn the difference between vulnerable SQL queries and secure Laravel database queries.
            </p>
        </div>
    </div>

    <!-- Features -->
    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h5>🔍 Search</h5>
                    <p class="text-muted mb-0">
                        Search users by Name or Email.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h5>📄 Pagination</h5>
                    <p class="text-muted mb-0">
                        Display users with page navigation.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body text-center">
                    <h5>📊 Statistics</h5>
                    <p class="text-muted mb-0">
                        Total, Admin and Normal User counts.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h5>📥 CSV Export</h5>
                    <p class="text-muted mb-0">
                        Export search results to CSV.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Vulnerable & Safe -->
    <div class="row">

        <!-- Vulnerable -->
        <div class="col-lg-6">

            <div class="card border-danger shadow-sm mb-4">

                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        ❌ Vulnerable Examples
                    </h4>
                </div>

                <div class="card-body">

                    <p>
                        These examples intentionally demonstrate SQL Injection vulnerabilities.
                    </p>

                    <ul class="list-group">

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            Raw SQL Query

                            <a href="{{ route('unsafe.raw') }}"
                                class="btn btn-danger btn-sm">
                                Open
                            </a>

                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            whereRaw() Without Binding

                            <a href="{{ route('unsafe.whereRaw') }}"
                                class="btn btn-danger btn-sm">
                                Open
                            </a>

                        </li>

                    </ul>

                    <div class="alert alert-danger mt-3 mb-0">

                        <strong>Warning</strong>

                        <br>

                        Never concatenate user input directly into SQL queries.

                    </div>

                </div>

            </div>

        </div>

        <!-- Safe -->
        <div class="col-lg-6">

            <div class="card border-success shadow-sm mb-4">

                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        ✅ Safe Examples
                    </h4>
                </div>

                <div class="card-body">

                    <p>
                        These methods automatically protect your application from SQL Injection.
                    </p>

                    <ul class="list-group">

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            Parameterized SQL

                            <a href="{{ route('safe.parameterized') }}"
                                class="btn btn-success btn-sm">
                                Open
                            </a>

                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            Eloquent ORM

                            <a href="{{ route('safe.eloquent') }}"
                                class="btn btn-success btn-sm">
                                Open
                            </a>

                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            Query Builder

                            <a href="{{ route('safe.queryBuilder') }}"
                                class="btn btn-success btn-sm">
                                Open
                            </a>

                        </li>

                    </ul>

                    <div class="alert alert-success mt-3 mb-0">

                        <strong>Recommended</strong>

                        <br>

                        Always use Eloquent, Query Builder or Prepared Statements.

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- SQL Injection Payloads -->
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">
                Common SQL Injection Payloads
            </h4>
        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover">

                <thead class="table-light">

                    <tr>

                        <th width="40%">
                            Payload
                        </th>

                        <th>
                            Description
                        </th>

                    </tr>

                </thead>

                <tbody>

                    <tr>
                        <td><code>' OR '1'='1</code></td>
                        <td>Bypass WHERE condition</td>
                    </tr>

                    <tr>
                        <td><code>' UNION SELECT * FROM users --</code></td>
                        <td>UNION-based Injection</td>
                    </tr>

                    <tr>
                        <td><code>'; DROP TABLE users; --</code></td>
                        <td>Attempts destructive SQL execution</td>
                    </tr>

                    <tr>
                        <td><code>admin' --</code></td>
                        <td>Comments out the remaining SQL statement</td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

    <!-- Best Practices -->
    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                Laravel Security Best Practices
            </h4>
        </div>

        <div class="card-body">

            <ol class="mb-0">

                <li>Use Eloquent ORM.</li>

                <li>Use Laravel Query Builder.</li>

                <li>Use Prepared Statements with parameter binding.</li>

                <li>Validate every user input.</li>

                <li>Never concatenate SQL strings.</li>

                <li>Avoid unsafe <code>whereRaw()</code> usage.</li>

                <li>Escape output in Blade.</li>

                <li>Use authorization and authentication.</li>

                <li>Keep Laravel updated.</li>

                <li>Log suspicious activities.</li>

            </ol>

        </div>

    </div>

</div>

@endsection