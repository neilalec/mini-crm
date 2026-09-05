<?php

namespace App\Http\Controllers;

use App\Events\LeadChanged;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $request->user()->business()->firstOrCreate(
            [],
            ['name' => "{$request->user()->name}'s Business", 'slug' => Str::slug($request->user()->name).'-'.Str::lower(Str::random(5))]
        );
        $status = $request->string('status')->toString();
        $query = trim($request->string('q')->toString());
        $followUp = $request->string('follow_up')->toString();
        $contactState = $request->string('contact_state')->toString();

        $leads = Lead::query()
            ->where('business_id', $business->id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($query, function ($leadQuery) use ($query) {
                $leadQuery->where(function ($searchQuery) use ($query) {
                    $searchQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%")
                        ->orWhere('subject', 'like', "%{$query}%");
                });
            })
            ->when($contactState === 'unreplied', fn ($leadQuery) => $leadQuery->whereNull('contacted_at'))
            ->when($contactState === 'replied', fn ($leadQuery) => $leadQuery->whereNotNull('contacted_at'))
            ->when($followUp, function ($leadQuery) use ($followUp) {
                $today = now()->toDateString();

                if ($followUp === 'overdue') {
                    $leadQuery->whereNotNull('follow_up_date')->whereDate('follow_up_date', '<', $today);
                }

                if ($followUp === 'today') {
                    $leadQuery->whereNotNull('follow_up_date')->whereDate('follow_up_date', '=', $today);
                }

                if ($followUp === 'upcoming') {
                    $leadQuery->whereNotNull('follow_up_date')->whereDate('follow_up_date', '>', $today);
                }
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $leads->through(function (Lead $lead) {
            $lead->follow_up_date = $lead->follow_up_date?->format('Y-m-d');

            return $lead;
        });

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => [
                'status' => $status,
                'q' => $query,
                'follow_up' => $followUp,
                'contact_state' => $contactState,
            ],
            'statuses' => Lead::STATUSES,
            'businessId' => $business->id,
        ]);
    }

    public function show(Request $request, Lead $lead): Response
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $lead->business_id === $business->id, 403);
        if (! $lead->chat_token) {
            $lead->update(['chat_token' => Str::random(40)]);
        }

        $lead->load(['notes.user', 'messages', 'activities']);
        $lead->follow_up_date = $lead->follow_up_date?->format('Y-m-d');
        $templates = $business->replyTemplates()->latest()->get(['id', 'title', 'body']);

        return Inertia::render('Leads/Show', [
            'lead' => [
                ...$lead->toArray(),
                'activities' => $lead->activities
                    ->map(fn (LeadActivity $activity) => LeadActivity::present($activity))
                    ->values(),
            ],
            'statuses' => Lead::STATUSES,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $lead->business_id === $business->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(Lead::STATUSES)],
            'quote_amount' => ['nullable', 'numeric', 'min:0'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $originalStatus = $lead->status;
        $originalQuoteAmount = $lead->quote_amount;
        $originalFollowUpDate = $lead->follow_up_date?->format('Y-m-d');

        if (! empty($data['quote_amount']) && ! in_array($lead->status, ['won', 'lost'], true)) {
            $data['status'] = 'quoted';
        }

        if (($data['status'] ?? null) === 'contacted' && ! $lead->contacted_at) {
            $data['contacted_at'] = now();
        }

        $lead->update($data);
        $lead->refresh();

        if ($lead->status !== $originalStatus) {
            LeadActivity::record(
                $lead,
                'status_changed',
                $request->user()->name,
                "Status changed from {$originalStatus} to {$lead->status}.",
                [
                    'from' => $originalStatus,
                    'to' => $lead->status,
                ],
            );
        }

        if ((string) ($lead->quote_amount ?? '') !== (string) ($originalQuoteAmount ?? '')) {
            LeadActivity::record(
                $lead,
                'quote_updated',
                $request->user()->name,
                $lead->quote_amount === null
                    ? 'Quote removed.'
                    : 'Quote updated to $'.number_format((float) $lead->quote_amount, 2).'.',
                [
                    'from' => $originalQuoteAmount,
                    'to' => $lead->quote_amount,
                ],
            );
        }

        if (($lead->follow_up_date?->format('Y-m-d')) !== $originalFollowUpDate) {
            LeadActivity::record(
                $lead,
                'follow_up_updated',
                $request->user()->name,
                $lead->follow_up_date
                    ? 'Follow-up moved to '.$lead->follow_up_date->format('Y-m-d').'.'
                    : 'Follow-up cleared.',
                [
                    'from' => $originalFollowUpDate,
                    'to' => $lead->follow_up_date?->format('Y-m-d'),
                ],
            );
        }

        broadcast(new LeadChanged($business->id, 'updated', [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'status' => $lead->status,
            'follow_up_date' => $lead->follow_up_date?->format('Y-m-d'),
        ]));

        return redirect()->route('leads.index')->with('success', 'Lead updated.');
    }

    public function destroy(Request $request, Lead $lead): RedirectResponse
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $lead->business_id === $business->id, 403);

        $payload = ['id' => $lead->id];
        $lead->delete();
        broadcast(new LeadChanged($business->id, 'deleted', $payload));

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }
}
