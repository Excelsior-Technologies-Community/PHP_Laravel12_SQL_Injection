<?php

namespace App\Http\Controllers;

use App\Models\AttackLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttackLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $logs = AttackLog::when($search, function ($query) use ($search) {

            $query->where('payload', 'like', "%{$search}%")
                ->orWhere('ip_address', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalAttacks = AttackLog::count();

        $uniqueAttackers = AttackLog::distinct('ip_address')
            ->count('ip_address');

        $todayAttacks = AttackLog::whereDate(
            'created_at',
            today()
        )->count();

        $latestAttack = AttackLog::latest()->first();

        $topIp = AttackLog::select(
            'ip_address',
            DB::raw('count(*) as total')
        )
            ->groupBy('ip_address')
            ->orderByDesc('total')
            ->first();

        $commonPattern = AttackLog::select(
            'pattern',
            DB::raw('count(*) as total')
        )
            ->groupBy('pattern')
            ->orderByDesc('total')
            ->first();

        $securityScore = max(0, 100 - min($totalAttacks * 2, 100));

        return view('attack-logs.index', compact(

            'logs',

            'search',

            'totalAttacks',

            'todayAttacks',

            'latestAttack',

            'topIp',

            'commonPattern',

            'securityScore',

            'uniqueAttackers'

        ));
    }

    public function destroy($id)
    {
        AttackLog::findOrFail($id)->delete();

        return back()->with('success', 'Attack log deleted successfully.');
    }

    public function clear()
    {
        AttackLog::truncate();

        return back()->with('success', 'All attack logs cleared successfully.');
    }
}
