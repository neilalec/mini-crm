<?php

namespace App\Http\Controllers;

use App\Models\Lead;
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

        $lead->notes()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back();
    }
}
