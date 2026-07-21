<?php

namespace App\Http\Controllers;

use App\Events\LeadChanged;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadNoteController extends Controller
{
    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $lead->business_id === $business->id, 403);

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $note = $lead->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $note->load('user');
        LeadActivity::record($lead, 'note_added', $request->user()->name, $note->body);
        broadcast(new \App\Events\LeadNoteCreated($lead->id, $note->toArray()));

        return back();
    }
}
