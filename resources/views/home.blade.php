@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Laravel SQL Injection Prevention Demo</h3>
                </div>
                <div class="card-body">
                    <h4>What is SQL Injection?</h4>
                    <p>
                        SQL injection is a code injection technique that might destroy your database.
                        It is one of the most common web hacking techniques.
                    </p>

                    <h4>Demonstration Examples:</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card vulnerable mb-3">
                                <div class="card-header">
                                    <h5>Vulnerable Methods</h5>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li><a href="{{ route('unsafe.raw') }}">Raw SQL Queries</a> - Direct string
                                            concatenation</li>
                                        <li><a href="{{ route('unsafe.whereRaw') }}">whereRaw() without binding</a> - Unsafe
                                            raw expressions</li>
                                    </ul>
                                    <p class="text-danger">
                                        <strong>Warning:</strong> These methods are vulnerable to SQL injection attacks!
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card safe mb-3">
                                <div class="card-header">
                                    <h5>Safe Methods</h5>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li><a href="{{ route('safe.parameterized') }}">Parameterized Queries</a> - Using
                                            placeholders</li>
                                        <li><a href="{{ route('safe.eloquent') }}">Eloquent ORM</a> - Laravel's built-in ORM
                                        </li>
                                        <li><a href="{{ route('safe.queryBuilder') }}">Query Builder</a> - Fluent interface
                                        </li>
                                    </ul>
                                    <p class="text-success">
                                        <strong>Safe:</strong> These methods automatically prevent SQL injection.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4>Best Practices to Prevent SQL Injection:</h4>
                    <ol>
                        <li><strong>Use Eloquent ORM</strong> - Laravel's ORM automatically escapes inputs</li>
                        <li><strong>Use Query Builder</strong> - The fluent interface parameterizes queries</li>
                        <li><strong>Use Prepared Statements</strong> - When writing raw SQL, always use parameter binding
                        </li>
                        <li><strong>Avoid Raw Expressions</strong> - Don't use `whereRaw()`, `selectRaw()` without binding
                        </li>
                        <li><strong>Validate Input</strong> - Always validate user input with Laravel Validation</li>
                        <li><strong>Use Whitelists</strong> - For dynamic column/table names, use whitelists</li>
                    </ol>

                    <h4>Example Attacks to Try:</h4>
                    <div class="mb-3">
                        <pre>
    1. Basic Injection: ' OR '1'='1
    2. Union Attack: ' UNION SELECT * FROM users --
    3. Drop Table: '; DROP TABLE users; --
    4. Comment Attack: admin' -- 
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection