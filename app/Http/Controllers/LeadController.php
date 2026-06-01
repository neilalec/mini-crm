<?php

namespace App\Http\Controllers;

use App\Models\Lead;
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

        $leads = Lead::query()
            ->where('business_id', $business->id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'filters' => ['status' => $status],
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function show(Request $request, Lead $lead): Response
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $lead->business_id === $business->id, 403);

        $lead->load(['notes.user']);
        $templates = $business->replyTemplates()->latest()->get();

        return Inertia::render('Leads/Show', [
            'lead' => $lead,
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
            'quote_notes' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        if (($data['status'] ?? null) === 'contacted' && ! $lead->contacted_at) {
            $data['contacted_at'] = now();
        }

        $lead->update($data);

        return back();
    }
}
