<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadMessageController;
use App\Http\Controllers\LeadNoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicLeadChatController;
use App\Http\Controllers\PublicEnquiryController;
use App\Http\Controllers\ReplyTemplateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/{business:slug}/enquire', [PublicEnquiryController::class, 'create'])->name('enquiry.create');
Route::post('/{business:slug}/enquire', [PublicEnquiryController::class, 'store'])->name('enquiry.store');
Route::get('/chat/{token}', [PublicLeadChatController::class, 'show'])->name('chat.show');
Route::post('/chat/{token}', [PublicLeadChatController::class, 'store'])->name('chat.store');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('leads.notes.store');
    Route::post('/leads/{lead}/messages', [LeadMessageController::class, 'store'])->name('leads.messages.store');

    Route::get('/templates', [ReplyTemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates', [ReplyTemplateController::class, 'store'])->name('templates.store');
    Route::patch('/templates/{template}', [ReplyTemplateController::class, 'update'])->name('templates.update');
    Route::delete('/templates/{template}', [ReplyTemplateController::class, 'destroy'])->name('templates.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
