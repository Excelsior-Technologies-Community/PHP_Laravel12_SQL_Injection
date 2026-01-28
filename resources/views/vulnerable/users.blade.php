@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card {{ str_contains($method, 'Unsafe') ? 'vulnerable' : 'safe' }}">
            <div class="card-header">
                <h3>{{ $method }} Example</h3>
                @if(str_contains($method, 'Unsafe'))
                    <span class="badge bg-danger">VULNERABLE</span>
                @else
                    <span class="badge bg-success">SAFE</span>
                @endif
            </div>
            <div class="card-body">
                <form method="GET" action="" class="mb-4">
                    <div class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search users..." 
                               value="{{ $search }}">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                    <small class="form-text text-muted">
                        @if(str_contains($method, 'Unsafe'))
                            ⚠️ Try malicious inputs like: <code>' OR '1'='1</code> or <code>'; DROP TABLE users; --</code>
                        @else
                            ✅ This method is safe from SQL injection
                        @endif
                    </small>
                </form>

                <h5>Query Details:</h5>
                <div class="mb-3">
                    @if($method == 'Unsafe Raw SQL')
                        <pre>DB::select("SELECT * FROM users WHERE name LIKE '%$search%' OR email LIKE '%$search%'")</pre>
                    @elseif($method == 'Unsafe whereRaw()')
                        <pre>User::whereRaw("name LIKE '%$search%'")->orWhereRaw("email LIKE '%$search%'")</pre>
                    @elseif($method == 'Safe Parameterized SQL')
                        <pre>DB::select("SELECT * FROM users WHERE name LIKE ? OR email LIKE ?", ["%$search%", "%$search%"])</pre>
                    @elseif($method == 'Safe Eloquent ORM')
                        <pre>User::where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")</pre>
                    @elseif($method == 'Safe Query Builder')
                        <pre>DB::table('users')->where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")</pre>
                    @endif
                </div>

                @php
                    // Convert array to collection if needed
                    $usersCollection = is_array($users) ? collect($users) : $users;
                @endphp

                <h5>Results ({{ $usersCollection->count() }} found):</h5>
                
                @if($usersCollection->count() > 0)
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Is Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usersCollection as $user)
                                <tr>
                                    <td>{{ is_object($user) ? $user->id : $user['id'] }}</td>
                                    <td>{{ is_object($user) ? $user->name : $user['name'] }}</td>
                                    <td>{{ is_object($user) ? $user->email : $user['email'] }}</td>
                                    <td>
                                        @php
                                            if (is_object($user)) {
                                                $isAdmin = $user->is_admin ?? false;
                                            } else {
                                                $isAdmin = $user['is_admin'] ?? false;
                                            }
                                        @endphp
                                        @if($isAdmin)
                                            <span class="badge bg-danger">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">User</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">No users found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection