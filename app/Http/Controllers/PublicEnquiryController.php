<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $business->leads()->create($data);

        return back()->with('success', 'Thanks, your enquiry has been sent.');
    }
}
