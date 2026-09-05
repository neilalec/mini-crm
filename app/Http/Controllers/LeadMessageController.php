<?php

namespace App\Http\Controllers;

use App\Events\LeadChanged;
use App\Events\LeadMessageCreated;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LeadMessageController extends Controller
{
    public function store(Request $request, Lead $lead): RedirectResponse
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $lead->business_id === $business->id, 403);

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $message = $lead->messages()->create([
            'sender_type' => 'business',
            'sender_name' => $request->user()->name,
            'body' => $data['body'],
        ]);
        LeadActivity::record($lead, 'business_message', $request->user()->name, $message->body);

        Mail::raw($data['body'], function ($message) use ($lead) {
            $message->to($lead->email, $lead->name)->subject('Message about your enquiry');
        });

        $previousStatus = $lead->status;
        $lead->update([
            'contacted_at' => $lead->contacted_at ?? now(),
            'status' => in_array($lead->status, ['won', 'lost', 'quoted'], true) ? $lead->status : 'contacted',
        ]);

        if ($lead->status !== $previousStatus) {
            LeadActivity::record(
                $lead,
                'status_changed',
                $request->user()->name,
                "Status changed from {$previousStatus} to {$lead->status}.",
                [
                    'from' => $previousStatus,
                    'to' => $lead->status,
                ],
            );
        }

        broadcast(new LeadMessageCreated($lead->chat_token, $message->toArray()));
        broadcast(new LeadChanged($lead->business_id, 'updated', [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'status' => $lead->status,
            'follow_up_date' => optional($lead->follow_up_date)?->format('Y-m-d'),
        ]));

        return back()->with('success', 'Message sent.');
    }
}
