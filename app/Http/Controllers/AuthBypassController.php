<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthBypassController extends Controller
{
    /**
     * Display Vulnerable Authentication & Login Bypass Arena
     */
    public function index()
    {
        $currentUser = Auth::user();
        $adminUser = User::where('is_admin', true)->first();

        return view('auth-bypass.index', compact('currentUser', 'adminUser'));
    }

    /**
     * Attempt login using Unsafe Concatenated SQL
     */
    public function loginUnsafe(Request $request)
    {
        $email = $request->input('email', '');
        $password = $request->input('password', '');

        $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $start = microtime(true);
        $executedSuccessfully = false;
        $errorMessage = null;
        $matchedUser = null;

        try {
            // Execute raw concatenated query
            $results = DB::select($sql);
            $executedSuccessfully = true;

            if (!empty($results)) {
                $userRecord = $results[0];
                $matchedUser = User::find($userRecord->id);
                if ($matchedUser) {
                    Auth::login($matchedUser);
                }
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $matchedUser !== null,
                'sql' => $sql,
                'executed' => $executedSuccessfully,
                'error' => $errorMessage,
                'user' => $matchedUser ? [
                    'id' => $matchedUser->id,
                    'name' => $matchedUser->name,
                    'email' => $matchedUser->email,
                    'is_admin' => (bool) $matchedUser->is_admin,
                ] : null,
                'duration_ms' => $duration,
                'is_bypass' => $matchedUser !== null && empty($password),
            ]);
        }

        if ($matchedUser) {
            return back()->with('success', "Logged in successfully as {$matchedUser->name} (" . ($matchedUser->is_admin ? 'ADMIN' : 'User') . ") via SQL Injection bypass!");
        }

        return back()->with('error', $errorMessage ? "SQL Error: {$errorMessage}" : "Authentication failed. Invalid credentials.");
    }

    /**
     * Attempt login using Secure Laravel Authentication
     */
    public function loginSafe(Request $request)
    {
        $email = $request->input('email', '');
        $password = $request->input('password', '');

        $start = microtime(true);
        $success = false;

        // Secure authentication using parameterized Eloquent + Bcrypt Hash verify
        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            $success = true;
        }

        $duration = round((microtime(true) - $start) * 1000, 2);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'safe_method' => 'User::where("email", ?)->first() + Hash::check()',
                'user' => $success ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => (bool) $user->is_admin,
                ] : null,
                'duration_ms' => $duration,
            ]);
        }

        if ($success) {
            return back()->with('success', "Securely logged in as {$user->name}.");
        }

        return back()->with('error', "Secure Authentication Failed: The payload was safely treated as literal text and rejected.");
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::logout();
        return back()->with('success', 'Logged out successfully.');
    }
}
