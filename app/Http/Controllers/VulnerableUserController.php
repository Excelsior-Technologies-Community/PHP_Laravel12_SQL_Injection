<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VulnerableUserController extends Controller
{
    /**
     * Dashboard Statistics
     */
    private function statistics($users = null)
    {
        return [
            'totalUsers'   => User::count(),
            'adminUsers'   => User::where('is_admin', 1)->count(),
            'normalUsers'  => User::where('is_admin', 0)->count(),
            'searchResult' => $users ? $users->total() : User::count(),
        ];
    }

    /**
     * ===========================================
     * UNSAFE RAW SQL
     * ===========================================
     */
    public function unsafeSearch(Request $request)
    {
        $search = $request->search ?? '';

        if ($request->has('export')) {
            $users = DB::select("
                SELECT * FROM users
                WHERE name LIKE '%$search%'
                OR email LIKE '%$search%'
            ");

            return $this->exportCsv($users);
        }

        $users = User::whereRaw("
                    name LIKE '%$search%'
                    OR email LIKE '%$search%'
                ")
            ->paginate(5)
            ->appends($request->all());

        return view('vulnerable.users', [
            'users'  => $users,
            'search' => $search,
            'method' => 'Unsafe Raw SQL'
        ] + $this->statistics($users));
    }

    /**
     * ===========================================
     * UNSAFE WHERERAW
     * ===========================================
     */
    public function unsafeWhereRaw(Request $request)
    {
        $search = $request->search ?? '';

        if ($request->has('export')) {

            $users = User::whereRaw("name LIKE '%$search%'")
                ->orWhereRaw("email LIKE '%$search%'")
                ->get();

            return $this->exportCsv($users);
        }

        $users = User::whereRaw("name LIKE '%$search%'")
            ->orWhereRaw("email LIKE '%$search%'")
            ->paginate(5)
            ->appends($request->all());

        return view('vulnerable.users', [
            'users'  => $users,
            'search' => $search,
            'method' => 'Unsafe whereRaw()'
        ] + $this->statistics($users));
    }

    /**
     * ===========================================
     * SAFE PARAMETERIZED SQL
     * ===========================================
     */
    public function safeParameterized(Request $request)
    {
        $search = $request->search ?? '';

        if ($request->has('export')) {

            $users = DB::select(
                "SELECT * FROM users
                 WHERE name LIKE ?
                 OR email LIKE ?",
                ["%$search%", "%$search%"]
            );

            return $this->exportCsv($users);
        }

        $users = User::where(function ($query) use ($search) {

            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->paginate(5)
            ->appends($request->all());

        return view('vulnerable.users', [
            'users'  => $users,
            'search' => $search,
            'method' => 'Safe Parameterized SQL'
        ] + $this->statistics($users));
    }

    /**
     * ===========================================
     * SAFE ELOQUENT
     * ===========================================
     */
    public function safeEloquent(Request $request)
    {
        $search = $request->search ?? '';

        if ($request->has('export')) {

            $users = User::where(function ($query) use ($search) {

                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })->get();

            return $this->exportCsv($users);
        }

        $users = User::where(function ($query) use ($search) {

            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->paginate(5)
            ->appends($request->all());

        return view('vulnerable.users', [
            'users'  => $users,
            'search' => $search,
            'method' => 'Safe Eloquent ORM'
        ] + $this->statistics($users));
    }

    /**
     * ===========================================
     * SAFE QUERY BUILDER
     * ===========================================
     */
    public function safeQueryBuilder(Request $request)
    {
        $search = $request->search ?? '';

        if ($request->has('export')) {

            $users = DB::table('users')
                ->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->get();

            return $this->exportCsv($users);
        }

        $users = User::where(function ($query) use ($search) {

            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->paginate(5)
            ->appends($request->all());

        return view('vulnerable.users', [
            'users'  => $users,
            'search' => $search,
            'method' => 'Safe Query Builder'
        ] + $this->statistics($users));
    }

    /**
     * ===========================================
     * EXPORT CSV
     * ===========================================
     */
    private function exportCsv($users)
    {
        $response = new StreamedResponse(function () use ($users) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Name',
                'Email',
                'Admin'
            ]);

            foreach ($users as $user) {

                fputcsv($handle, [

                    $user->id,
                    $user->name,
                    $user->email,
                    isset($user->is_admin) && $user->is_admin ? 'Yes' : 'No'

                ]);
            }

            fclose($handle);
        });

        $response->headers->set(
            'Content-Type',
            'text/csv'
        );

        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="users.csv"'
        );

        return $response;
    }
}
