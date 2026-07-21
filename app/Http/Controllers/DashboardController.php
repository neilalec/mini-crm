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
        $today = now()->toDateString();

        $totals = Lead::query()
            ->where('business_id', $business->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $followUpQuery = Lead::query()
            ->where('business_id', $business->id)
            ->whereNotNull('follow_up_date')
            ->whereNotIn('status', ['won', 'lost']);

        $followUpSummary = [
            'overdue' => (clone $followUpQuery)->whereDate('follow_up_date', '<', $today)->count(),
            'today' => (clone $followUpQuery)->whereDate('follow_up_date', '=', $today)->count(),
            'upcoming' => (clone $followUpQuery)->whereDate('follow_up_date', '>', $today)->count(),
        ];

        $followUpBuckets = [
            'overdue' => $this->formatLeads(
                (clone $followUpQuery)
                    ->whereDate('follow_up_date', '<', $today)
                    ->orderBy('follow_up_date')
                    ->limit(5)
                    ->get(['id', 'name', 'status', 'follow_up_date'])
            ),
            'today' => $this->formatLeads(
                (clone $followUpQuery)
                    ->whereDate('follow_up_date', '=', $today)
                    ->orderBy('follow_up_date')
                    ->limit(5)
                    ->get(['id', 'name', 'status', 'follow_up_date'])
            ),
            'upcoming' => $this->formatLeads(
                (clone $followUpQuery)
                    ->whereDate('follow_up_date', '>', $today)
                    ->orderBy('follow_up_date')
                    ->limit(5)
                    ->get(['id', 'name', 'status', 'follow_up_date'])
            ),
        ];

        $inboxLeads = Lead::query()
            ->where('business_id', $business->id)
            ->latest()
            ->limit(10)
            ->get(['id', 'name', 'email', 'status', 'follow_up_date']);
        $inboxLeads = $this->formatLeads($inboxLeads);

        return Inertia::render('Dashboard', [
            'business' => $business->only(['id', 'name', 'slug']),
            'totals' => $totals,
            'followUpSummary' => $followUpSummary,
            'followUpBuckets' => $followUpBuckets,
            'inboxLeads' => $inboxLeads,
        ]);
    }

    private function formatLeads($leads)
    {
        return $leads->transform(function (Lead $lead) {
            $lead->follow_up_date = $lead->follow_up_date?->format('Y-m-d');

            return $lead;
        });
    }
}
