@extends('layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('content')

<h2 class="mb-4">
    SQL Injection Attack Logs
</h2>

<div class="card shadow-sm mb-4">

    <div class="card-header bg-success text-white">

        <strong>

            Security Dashboard

        </strong>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3 text-center">

                <h6>Security Score</h6>

                <h1 class="text-success">

                    {{ $securityScore }}%

                </h1>

            </div>

            <div class="col-md-3 text-center">

                <h6>Total Attacks</h6>

                <h1 class="text-danger">

                    {{ $totalAttacks }}

                </h1>

            </div>

            <div class="col-md-3 text-center">

                <h6>Top Attacker IP</h6>

                <strong>

                    {{ $topIp->ip_address ?? 'N/A' }}

                </strong>

                <br>

                <small>

                    {{ $topIp->total ?? 0 }} Attempts

                </small>

            </div>

            <div class="col-md-3 text-center">

                <h6>Most Common Pattern</h6>

                <strong>

                    {{ $commonPattern->pattern ?? 'N/A' }}

                </strong>

                <br>

                <small>

                    {{ $commonPattern->total ?? 0 }} Times

                </small>

            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-body">

        <h5>
            Security Health
        </h5>

        <div class="progress" style="height:30px">

            <div
                class="progress-bar bg-success"
                style="width:{{ $securityScore }}%">

                {{ $securityScore }}%

            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-header bg-primary text-white">

        Security Recommendations

    </div>

    <div class="card-body">

        <ul class="mb-0">

            <li>Always use Eloquent ORM.</li>

            <li>Use Query Builder instead of raw SQL.</li>

            <li>Never concatenate user input.</li>

            <li>Validate all request parameters.</li>

            <li>Use parameter binding.</li>

            <li>Monitor attack attempts.</li>

            <li>Review suspicious IP addresses.</li>

            <li>Keep Laravel updated.</li>

        </ul>

    </div>

</div>

<div class="row mb-4">

    <div class="col-md-4">

        <div class="card border-danger shadow-sm">

            <div class="card-body text-center">

                <h6>Unique Attackers</h6>

                <h2 class="text-danger">

                    {{ $uniqueAttackers }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-warning shadow-sm">

            <div class="card-body text-center">

                <h6>Today's Attacks</h6>

                <h2 class="text-warning">

                    {{ $todayAttacks }}

                </h2>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-primary shadow-sm">

            <div class="card-body text-center">

                <h6>Latest Attack</h6>

                @if($latestAttack)

                <strong>

                    {{ $latestAttack->ip_address }}

                </strong>

                <br>

                <small>

                    {{ $latestAttack->created_at->diffForHumans() }}

                </small>

                @else

                No attacks

                @endif

            </div>

        </div>

    </div>

</div>

<div class="card shadow-sm mb-4">

    <div class="card-body">

        <form>

            <div class="row">

                <div class="col-md-9">

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Search Payload or IP">

                </div>

                <div class="col-md-3 d-flex gap-2">

                    <button class="btn btn-primary">

                        Search

                    </button>

                    <a href="{{ route('attack.logs') }}"
                        class="btn btn-secondary">

                        Reset

                    </a>

                </div>

            </div>

        </form>

    </div>

</div>

<div class="card shadow-sm">

    <div class="card-header bg-dark text-white d-flex justify-content-between">

        <strong>

            Attack History

        </strong>

        <form method="POST"
            action="{{ route('attack.logs.clear') }}">

            @csrf

            @method('DELETE')

            <button
                class="btn btn-danger btn-sm"
                onclick="return confirm('Clear all logs?')">

                Clear All

            </button>

        </form>

    </div>

    <div class="card-body">

        @if($logs->count())

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Severity</th>

                        <th>IP</th>

                        <th>Route</th>

                        <th>Method</th>

                        <th>Payload</th>

                        <th>Pattern</th>

                        <th>Date</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($logs as $log)

                    <tr @class([ 'table-danger'=> Str::contains(strtoupper($log->pattern), 'DROP'),
                        'table-warning' => Str::contains(strtoupper($log->pattern), 'UNION')
                        || Str::contains(strtoupper($log->pattern), 'OR'),
                        ])>

                        <td>{{ $log->id }}</td>

                        <td>

                            @if(Str::contains(strtoupper($log->pattern), 'DROP'))

                            <span class="badge bg-danger">Critical</span>

                            @elseif(
                            Str::contains(strtoupper($log->pattern), 'UNION') ||
                            Str::contains(strtoupper($log->pattern), 'OR')
                            )

                            <span class="badge bg-warning">High</span>

                            @elseif(Str::contains(strtoupper($log->pattern), 'SELECT'))

                            <span class="badge bg-info">Medium</span>

                            @else

                            <span class="badge bg-secondary">Low</span>

                            @endif

                        </td>

                        <td>{{ $log->ip_address }}</td>

                        <td>{{ $log->route }}</td>

                        <td>{{ $log->method }}</td>

                        <td>

                            <code class="text-danger">

                                {{ Str::limit($log->payload, 80) }}

                            </code>

                        </td>

                        <td>

                            @if(Str::contains(strtoupper($log->pattern), 'DROP'))

                            <span class="badge bg-danger">Danger</span>

                            @elseif(
                            Str::contains(strtoupper($log->pattern), 'UNION') ||
                            Str::contains(strtoupper($log->pattern), 'OR')
                            )

                            <span class="badge bg-warning">High</span>

                            @elseif(Str::contains(strtoupper($log->pattern), 'SELECT'))

                            <span class="badge bg-info">Medium</span>

                            @else

                            <span class="badge bg-secondary">Low</span>

                            @endif

                            <br>

                            <small>

                                {{ $log->pattern }}

                            </small>

                        </td>

                        <td>

                            {{ $log->created_at->format('d M Y H:i') }}

                        </td>

                        <td>

                            <form
                                method="POST"
                                action="{{ route('attack.logs.delete',$log->id) }}">

                                @csrf

                                @method('DELETE')

                                <button
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete?')">

                                    Delete

                                </button>

                            </form>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="mt-3">

            {{ $logs->links() }}

        </div>

        @else

        <div class="alert alert-success">

            No SQL Injection attacks detected.

        </div>

        @endif

    </div>

</div>

@endsection