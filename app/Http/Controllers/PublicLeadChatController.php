<?php

namespace App\Http\Controllers;

use App\Events\LeadMessageCreated;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicLeadChatController extends Controller
{
    public function show(string $token): Response
    {
        $lead = Lead::query()->where('chat_token', $token)->firstOrFail();
        $lead->load(['messages', 'business']);

        return Inertia::render('Public/LeadChat', [
            'lead' => $lead->only(['id', 'name', 'email', 'phone', 'subject', 'message', 'chat_token']),
            'businessName' => $lead->business->name,
            'messages' => $lead->messages,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $lead = Lead::query()->where('chat_token', $token)->firstOrFail();

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $message = $lead->messages()->create([
            'sender_type' => 'customer',
            'sender_name' => $data['name'] ?? $lead->name,
            'body' => $data['body'],
        ]);
        LeadActivity::record($lead, 'customer_message', $message->sender_name, $message->body);
        broadcast(new LeadMessageCreated($lead->chat_token, $message->toArray()));

        return back()->with('success', 'Message sent.');
    }
}
