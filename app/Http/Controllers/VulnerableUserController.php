<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class VulnerableUserController extends Controller
{
    /**
     * UNSAFE: Raw SQL query vulnerable to SQL Injection
     */
    public function unsafeSearch(Request $request)
    {
        $search = $request->input('search', '');
        
        // ⚠️ VULNERABLE: Direct string concatenation
        $users = DB::select("SELECT * FROM users WHERE name LIKE '%$search%' OR email LIKE '%$search%'");
        
        return view('vulnerable.users', [
            'users' => $users,
            'search' => $search,
            'method' => 'Unsafe Raw SQL'
        ]);
    }

    /**
     * UNSAFE: Using whereRaw without parameter binding
     */
    public function unsafeWhereRaw(Request $request)
    {
        $search = $request->input('search', '');
        
        // ⚠️ VULNERABLE: whereRaw with direct concatenation
        $users = User::whereRaw("name LIKE '%$search%'")
                    ->orWhereRaw("email LIKE '%$search%'")
                    ->get();
        
        return view('vulnerable.users', [
            'users' => $users,
            'search' => $search,
            'method' => 'Unsafe whereRaw()'
        ]);
    }

    /**
     * SAFE: Using parameter binding with raw SQL
     */
    public function safeParameterized(Request $request)
    {
        $search = $request->input('search', '');
        
        // ✅ SAFE: Parameter binding
        $users = DB::select(
            "SELECT * FROM users WHERE name LIKE ? OR email LIKE ?",
            ["%$search%", "%$search%"]
        );
        
        return view('vulnerable.users', [
            'users' => $users,
            'search' => $search,
            'method' => 'Safe Parameterized SQL'
        ]);
    }

    /**
     * SAFE: Using Eloquent ORM (Laravel Query Builder)
     */
    public function safeEloquent(Request $request)
    {
        $search = $request->input('search', '');
        
        // ✅ SAFE: Eloquent ORM automatically parameterizes queries
        $users = User::where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->get();
        
        return view('vulnerable.users', [
            'users' => $users,
            'search' => $search,
            'method' => 'Safe Eloquent ORM'
        ]);
    }

    /**
     * SAFE: Using Query Builder with where()
     */
    public function safeQueryBuilder(Request $request)
    {
        $search = $request->input('search', '');
        
        // ✅ SAFE: Query Builder with parameter binding
        $users = DB::table('users')
                    ->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->get();
        
        return view('vulnerable.users', [
            'users' => $users,
            'search' => $search,
            'method' => 'Safe Query Builder'
        ]);
    }
}