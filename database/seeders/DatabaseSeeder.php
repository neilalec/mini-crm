<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\ReplyTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Northside Plumbing',
            'slug' => 'northside-plumbing',
        ]);

        ReplyTemplate::create([
            'business_id' => $business->id,
            'title' => 'Thanks for your enquiry',
            'body' => 'Thanks for getting in touch. I have received your enquiry and will follow up with the next steps shortly.',
        ]);

        ReplyTemplate::create([
            'business_id' => $business->id,
            'title' => 'Quote follow-up',
            'body' => 'I wanted to check whether you had any questions about the quote. I am happy to clarify anything before you decide.',
        ]);

        $nextWorkingDay = Carbon::tomorrow();
        while ($nextWorkingDay->isWeekend()) {
            $nextWorkingDay->addDay();
        }

        $lead = Lead::create([
            'business_id' => $business->id,
            'name' => 'Jamie Customer',
            'email' => 'jamie@example.com',
            'phone' => '07123456789',
            'subject' => 'Boiler issue',
            'message' => 'The boiler keeps losing pressure and I would like someone to take a look.',
            'status' => 'new',
            'follow_up_date' => $nextWorkingDay->toDateString(),
        ]);

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

        $quotedLead = Lead::create([
            'business_id' => $business->id,
            'name' => 'Taylor Prospect',
            'email' => 'taylor@example.com',
            'phone' => '07987654321',
            'subject' => 'Bathroom refit',
            'message' => 'Looking for a quote for a small bathroom refit next month.',
            'status' => 'quoted',
            'quote_amount' => 850.00,
            'follow_up_date' => Carbon::today()->addDays(3)->toDateString(),
            'contacted_at' => Carbon::now()->subDay(),
        ]);

        LeadActivity::record(
            $quotedLead,
            'enquiry_received',
            $quotedLead->name,
            $quotedLead->message,
            [
                'email' => $quotedLead->email,
                'phone' => $quotedLead->phone,
                'subject' => $quotedLead->subject,
            ],
        );

        LeadActivity::record(
            $quotedLead,
            'quote_updated',
            $user->name,
            'Quote updated to $850.00.',
            [
                'from' => null,
                'to' => 850.00,
            ],
        );
    }
}
