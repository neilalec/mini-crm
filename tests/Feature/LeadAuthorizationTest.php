<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_cannot_view_or_update_leads_from_another_business(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownersBusiness = Business::create([
            'user_id' => $owner->id,
            'name' => 'Northside Plumbing',
            'slug' => 'northside-plumbing',
        ]);

        Business::create([
            'user_id' => $otherUser->id,
            'name' => 'Southside Heating',
            'slug' => 'southside-heating',
        ]);

        $lead = Lead::create([
            'business_id' => $ownersBusiness->id,
            'name' => 'Jamie Customer',
            'email' => 'jamie@example.com',
            'subject' => 'Boiler issue',
            'message' => 'Need someone out on Monday.',
            'status' => 'new',
        ]);

        $this->actingAs($otherUser)
            ->get(route('leads.show', $lead))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->patch(route('leads.update', $lead), [
                'status' => 'won',
                'quote_amount' => '250.00',
                'follow_up_date' => now()->toDateString(),
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'new',
            'quote_amount' => null,
        ]);
    }
}
