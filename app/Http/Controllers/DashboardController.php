<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $business = $request->user()->business()->firstOrCreate(
            [],
            ['name' => "{$request->user()->name}'s Business", 'slug' => Str::slug($request->user()->name).'-'.Str::lower(Str::random(5))]
        );

        $totals = Lead::query()
            ->where('business_id', $business->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $upcomingFollowUps = Lead::query()
            ->where('business_id', $business->id)
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '>=', now()->toDateString())
            ->orderBy('follow_up_date')
            ->limit(5)
            ->get(['id', 'name', 'status', 'follow_up_date']);

        return Inertia::render('Dashboard', [
            'business' => $business->only(['name', 'slug']),
            'totals' => $totals,
            'upcomingFollowUps' => $upcomingFollowUps,
        ]);
    }
}
