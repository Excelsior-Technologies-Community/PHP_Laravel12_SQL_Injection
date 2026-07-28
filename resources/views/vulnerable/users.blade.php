@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- Page Header -->
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h2 class="mb-1">
                        {{ $method }}
                    </h2>

                    @if(str_contains($method,'Unsafe'))

                    <span class="badge bg-danger">
                        Vulnerable
                    </span>

                    @else

                    <span class="badge bg-success">
                        Safe
                    </span>

                    @endif

                </div>

                <a href="{{ route('home') }}"
                    class="btn btn-secondary">

                    Home

                </a>

            </div>

        </div>

    </div>

    <!-- Statistics -->

    <div class="row mb-4">

        <div class="col-lg-3 col-md-6">

            <div class="card border-primary shadow-sm">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Total Users
                    </h6>

                    <h2 class="fw-bold text-primary">

                        {{ $totalUsers }}

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-danger shadow-sm">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Admin Users
                    </h6>

                    <h2 class="fw-bold text-danger">

                        {{ $adminUsers }}

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-success shadow-sm">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Normal Users
                    </h6>

                    <h2 class="fw-bold text-success">

                        {{ $normalUsers }}

                    </h2>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-warning shadow-sm">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Search Results
                    </h6>

                    <h2 class="fw-bold text-warning">

                        {{ $searchResult }}

                    </h2>

                </div>

            </div>

        </div>

    </div>



    <!-- Query Details -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-dark text-white">

            <strong>

                Query Details

            </strong>

        </div>

        <div class="card-body">

            @if($method == 'Unsafe Raw SQL')

            <pre>
DB::select("SELECT * FROM users
WHERE name LIKE '%$search%'
OR email LIKE '%$search%'")
</pre>

            @elseif($method == 'Unsafe whereRaw()')

            <pre>
User::whereRaw("name LIKE '%$search%'")
    ->orWhereRaw("email LIKE '%$search%'")
</pre>

            @elseif($method == 'Safe Parameterized SQL')

            <pre>
DB::select(
"SELECT * FROM users
WHERE name LIKE ?
OR email LIKE ?",
["%$search%","%$search%"]
)
</pre>

            @elseif($method == 'Safe Eloquent ORM')

            <pre>
User::where('name','LIKE',"%$search%")
    ->orWhere('email','LIKE',"%$search%")
</pre>

            @elseif($method == 'Safe Query Builder')

            <pre>
DB::table('users')
    ->where('name','LIKE',"%$search%")
    ->orWhere('email','LIKE',"%$search%")
</pre>

            @endif

        </div>

    </div>

    <!-- Search Card -->

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-light">

            <strong>
                Search Users
            </strong>

        </div>

        <div class="card-body">

            <form method="GET"
                action="">

                <div class="row">

                    <div class="col-md-8 mb-2">

                        <input
                            type="text"
                            class="form-control"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by Name or Email">

                    </div>

                    <div class="col-md-4">

                        <div class="d-flex gap-2">

                            <button
                                class="btn btn-primary">

                                Search

                            </button>

                            <a
                                href="{{ url()->current() }}"
                                class="btn btn-secondary">

                                Reset

                            </a>

                            <button
                                name="export"
                                value="1"
                                class="btn btn-success">

                                Export CSV

                            </button>

                        </div>

                    </div>

                </div>

            </form>

            <hr>

            @if(str_contains($method,'Unsafe'))

            <div class="alert alert-danger mb-0">

                <strong>
                    Unsafe Example
                </strong>

                <br>

                Try searching with

                <code>' OR '1'='1</code>

                or

                <code>admin' --</code>

                to demonstrate SQL Injection.

            </div>

            @else

            <div class="alert alert-success mb-0">

                <strong>
                    Safe Example
                </strong>

                <br>

                Laravel automatically parameterizes the query,
                preventing SQL Injection attacks.

            </div>

            @endif

        </div>

    </div>

    <!-- Users Table -->

    <div class="card shadow-sm">

        <div class="card-header bg-primary text-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>

                    User List

                </strong>

                <span class="badge bg-light text-dark">

                    {{ $users->total() }} Records

                </span>

            </div>

        </div>

        <div class="card-body">

            @if($users->count())

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th width="80">
                                ID
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th width="140">
                                Role
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($users as $user)

                        <tr>

                            <td>

                                {{ $user->id }}

                            </td>

                            <td>

                                {{ $user->name }}

                            </td>

                            <td>

                                {{ $user->email }}

                            </td>

                            <td>

                                @if($user->is_admin)

                                <span class="badge bg-danger">

                                    Admin

                                </span>

                                @else

                                <span class="badge bg-secondary">

                                    User

                                </span>

                                @endif

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            @else

            <div class="alert alert-warning">

                No users found matching your search.

            </div>

            @endif

            <!-- Pagination -->

            @if($users->count())

            <div class="row mt-4">

                <div class="col-md-6 d-flex align-items-center">

                    <small class="text-muted">

                        Showing

                        <strong>{{ $users->firstItem() }}</strong>

                        to

                        <strong>{{ $users->lastItem() }}</strong>

                        of

                        <strong>{{ $users->total() }}</strong>

                        users

                    </small>

                </div>

                <div class="col-md-6 d-flex justify-content-end">

                    {{ $users->withQueryString()->links() }}

                </div>

            </div>

            @endif

        </div>

    </div>

    <!-- Footer Information -->

    <div class="card shadow-sm mt-4">

        <div class="card-body">

            @if(str_contains($method,'Unsafe'))

            <div class="alert alert-danger mb-0">

                <h5 class="mb-2">
                    ⚠ Vulnerable Example
                </h5>

                <p class="mb-2">
                    This example intentionally demonstrates how SQL Injection can occur when user input is directly concatenated into SQL queries.
                </p>

                <strong>Never use this approach in production.</strong>

            </div>

            @else

            <div class="alert alert-success mb-0">

                <h5 class="mb-2">
                    ✅ Safe Example
                </h5>

                <p class="mb-2">
                    This example uses Laravel's built-in protection through parameter binding, Eloquent ORM, or the Query Builder.
                </p>

                <strong>Recommended for all Laravel applications.</strong>

            </div>

            @endif

        </div>

    </div>

</div>

@endsection