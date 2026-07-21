<?php

namespace App\Http\Controllers;

use App\Events\LeadChanged;
use App\Models\Business;
use App\Models\LeadActivity;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class PublicEnquiryController extends Controller
{
    public function create(Business $business): Response
    {
        return Inertia::render('Public/EnquiryForm', [
            'business' => $business->only(['name', 'slug']),
        ]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $nextWorkingDay = Carbon::tomorrow();
        while ($nextWorkingDay->isWeekend()) {
            $nextWorkingDay->addDay();
        }
        $data['follow_up_date'] = $nextWorkingDay->toDateString();

        $lead = $business->leads()->create($data);
        LeadActivity::record(
            $lead,
            'enquiry_received',
            $lead->name,
            $lead->message,
            [
                'email' => $lead->email,
                'phone' => $lead->phone,
                'subject' => $lead->subject,
            ],
        );
        $chatLink = route('chat.show', $lead->chat_token);

        Mail::raw("Thanks for your enquiry with {$business->name}.\n\nUse this secure link to continue chat about your enquiry:\n{$chatLink}\n\nYour submitted details:\nName: {$lead->name}\nEmail: {$lead->email}\nPhone: ".($lead->phone ?: 'N/A')."\nSubject: ".($lead->subject ?: 'N/A')."\nMessage: {$lead->message}", function ($message) use ($lead, $business) {
            $message->to($lead->email, $lead->name)->subject("Your enquiry chat link - {$business->name}");
        });

        broadcast(new LeadChanged($business->id, 'created', [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'status' => $lead->status,
            'follow_up_date' => optional($lead->follow_up_date)->toDateString(),
        ]));

        return redirect()->route('chat.show', $lead->chat_token)->with([
            'success' => 'Thank you for your enquiry. We have received it and will be in touch as soon as possible.',
            'chat_notice' => "We emailed you a link which you can use to access this personal chat between you and {$business->name}.",
        ]);
    }
}
