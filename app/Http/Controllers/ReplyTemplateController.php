<?php

namespace App\Http\Controllers;

use App\Events\TemplateChanged;
use App\Models\ReplyTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ReplyTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $request->user()->business()->firstOrCreate(
            [],
            ['name' => "{$request->user()->name}'s Business", 'slug' => Str::slug($request->user()->name).'-'.Str::lower(Str::random(5))]
        );

        return Inertia::render('Templates/Index', [
            'templates' => $business->replyTemplates()->latest()->get(),
            'businessId' => $business->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $business = $request->user()->business()->first();
        abort_unless($business, 403);
        $template = $business->replyTemplates()->create($data);
        broadcast(new TemplateChanged($business->id, 'created', $template->toArray()));

        return back();
    }

    public function update(Request $request, ReplyTemplate $template): RedirectResponse
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $template->business_id === $business->id, 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $template->update($data);
        broadcast(new TemplateChanged($business->id, 'updated', $template->toArray()));

        return back();
    }

    public function destroy(Request $request, ReplyTemplate $template): RedirectResponse
    {
        $business = $request->user()->business()->first();
        abort_unless($business && $template->business_id === $business->id, 403);

        $payload = ['id' => $template->id];
        $template->delete();
        broadcast(new TemplateChanged($business->id, 'deleted', $payload));

        return back();
    }
}
