<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LeadReplyController extends Controller
{
    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $lead->business_id === $business->id, 403);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        Mail::raw($data['body'], function ($message) use ($lead, $data) {
            $message->to($lead->email, $lead->name)->subject($data['subject']);
        });

        $lead->notes()->create([
            'user_id' => $request->user()->id,
            'body' => "Sent reply: {$data['subject']}\n\n{$data['body']}",
        ]);

        $lead->update([
            'contacted_at' => $lead->contacted_at ?? now(),
            'status' => in_array($lead->status, ['won', 'lost', 'quoted'], true) ? $lead->status : 'contacted',
        ]);

        return back()->with('success', 'Reply sent to lead.');
    }
}
