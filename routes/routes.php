<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Invitation registration (accessible to guests)
Route::get('register/{token}', [InvitationController::class, 'showRegistrationForm'])->name('invitation.register');
Route::post('register/{token}', [InvitationController::class, 'register']);

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', App\Http\Controllers\DashboardController::class)->name('dashboard');
    
    // Users Directory
    Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    
    // Invitations
    Route::get('invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::delete('invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');
    
    // Cards
    Route::get('cards', [CardController::class, 'index'])->name('cards.index');
    Route::get('cards/create', [CardController::class, 'create'])->name('cards.create');
    Route::post('cards', [CardController::class, 'store'])->name('cards.store');
    Route::get('cards/{card}', [CardController::class, 'show'])->name('cards.show');
    Route::get('cards/{card}/edit', [CardController::class, 'edit'])->name('cards.edit');
    Route::put('cards/{card}', [CardController::class, 'update'])->name('cards.update');
    Route::delete('cards/{card}', [CardController::class, 'destroy'])->name('cards.destroy');
    Route::post('cards/{card}/submit-for-voting', [CardController::class, 'submitForVoting'])->name('cards.submit-for-voting');
    
    // Card Voting
    Route::get('voting-feed', [CardController::class, 'votingFeed'])->name('cards.voting-feed');
    Route::post('cards/{card}/vote', [CardController::class, 'vote'])->name('cards.vote');
    
    // Trades
    Route::get('trades', [TradeController::class, 'index'])->name('trades.index');
    Route::get('trades/create/{recipient?}', [TradeController::class, 'create'])->name('trades.create');
    Route::post('trades', [TradeController::class, 'store'])->name('trades.store');
    Route::get('trades/{trade}', [TradeController::class, 'show'])->name('trades.show');
    Route::post('trades/{trade}/accept', [TradeController::class, 'accept'])->name('trades.accept');
    Route::post('trades/{trade}/decline', [TradeController::class, 'decline'])->name('trades.decline');
    Route::post('trades/{trade}/cancel', [TradeController::class, 'cancel'])->name('trades.cancel');
});

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    // Admin Dashboard
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // User Management
    Route::get('users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('users/{user}', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('users/{user}/invitations', [InvitationController::class, 'increaseLimit'])->name('admin.users.increase-invitation-limit');
    
    // Card Management
    Route::get('cards', [AdminController::class, 'cards'])->name('admin.cards');
    Route::put('cards/{card}/status', [AdminController::class, 'updateCardStatus'])->name('admin.cards.update-status');
    Route::post('mint-cards', [AdminController::class, 'forceMint'])->name('admin.mint-cards');
    
    // Settings
    Route::get('settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::put('settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
});