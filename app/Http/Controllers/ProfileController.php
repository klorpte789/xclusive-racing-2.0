<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        $results = $user->raceResults()
            ->where('session_type', 'race')
            ->orderByDesc('race_scheduled_at')
            ->get();

        $totalRaces = $results->count();
        $wins       = $results->where('position', 1)->count();
        $podiums    = $results->whereIn('position', [1, 2, 3])->count();
        $winRate    = $totalRaces > 0 ? round(($wins / $totalRaces) * 100) : 0;

        $stats = compact('totalRaces', 'wins', 'podiums', 'winRate');

        return view('profile.show', compact('user', 'results', 'stats'));
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }
}
