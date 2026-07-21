<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LeadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_enquiry_creates_lead_with_next_working_day_follow_up_and_activity(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-07-17 10:00:00');

        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Northside Plumbing',
            'slug' => 'northside-plumbing',
        ]);

        $response = $this->post(route('enquiry.store', $business), [
            'name' => 'Jamie Customer',
            'email' => 'jamie@example.com',
            'phone' => '07123456789',
            'subject' => 'Boiler issue',
            'message' => 'Need someone out on Monday.',
        ]);

        $lead = Lead::firstOrFail();

        $response->assertRedirect(route('chat.show', $lead->chat_token, false));
        $this->assertDatabaseHas('leads', [
            'business_id' => $business->id,
            'name' => 'Jamie Customer',
            'status' => 'new',
        ]);
        $this->assertSame('2026-07-20', $lead->follow_up_date?->format('Y-m-d'));
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'enquiry_received',
            'actor_name' => 'Jamie Customer',
            'body' => 'Need someone out on Monday.',
        ]);
    }

    public function test_lead_inbox_can_filter_by_search_follow_up_and_contact_state(): void
    {
        Carbon::setTestNow('2026-07-17 10:00:00');

        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Northside Plumbing',
            'slug' => 'northside-plumbing',
        ]);

        Lead::create([
            'business_id' => $business->id,
            'name' => 'Alice Jones',
            'email' => 'alice@example.com',
            'phone' => '07111111111',
            'subject' => 'Kitchen leak',
            'message' => 'Please quote.',
            'status' => 'new',
            'follow_up_date' => '2026-07-16',
        ]);

        Lead::create([
            'business_id' => $business->id,
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'phone' => '07222222222',
            'subject' => 'Boiler service',
            'message' => 'Need a service.',
            'status' => 'contacted',
            'follow_up_date' => '2026-07-17',
            'contacted_at' => now(),
        ]);

        Lead::create([
            'business_id' => $business->id,
            'name' => 'Cara West',
            'email' => 'cara@example.com',
            'phone' => '07333333333',
            'subject' => 'Bathroom install',
            'message' => 'Future project.',
            'status' => 'quoted',
            'follow_up_date' => '2026-07-21',
        ]);

        $response = $this->actingAs($user)->get(route('leads.index', [
            'q' => 'Boiler',
            'follow_up' => 'today',
            'contact_state' => 'replied',
        ]));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Leads/Index')
            ->where('filters.q', 'Boiler')
            ->where('filters.follow_up', 'today')
            ->where('filters.contact_state', 'replied')
            ->has('leads.data', 1)
            ->where('leads.data.0.name', 'Bob Smith')
        );
    }

    public function test_updating_a_lead_records_status_quote_and_follow_up_activities(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Northside Plumbing',
            'slug' => 'northside-plumbing',
        ]);

        $lead = Lead::create([
            'business_id' => $business->id,
            'name' => 'Jamie Customer',
            'email' => 'jamie@example.com',
            'message' => 'Need someone out on Monday.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($user)->patch(route('leads.update', $lead), [
            'status' => 'contacted',
            'quote_amount' => '250.00',
            'follow_up_date' => '2026-07-21',
        ]);

        $response->assertRedirect(route('leads.index', absolute: false));
        $lead->refresh();

        $this->assertSame('quoted', $lead->status);
        $this->assertSame('2026-07-21', $lead->follow_up_date?->format('Y-m-d'));
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'status_changed',
        ]);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'quote_updated',
        ]);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'follow_up_updated',
        ]);
    }
}
