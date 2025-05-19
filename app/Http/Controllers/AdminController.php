<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Constructor to ensure only admins can access this controller
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user() || !Auth::user()->is_admin) {
                abort(403, 'Unauthorized action.');
            }

            return $next($request);
        });
    }

    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalCards = Card::count();

        $cardsByStatus = [
            'draft' => Card::where('status', 'draft')->count(),
            'voting' => Card::where('status', 'voting')->count(),
            'minted' => Card::where('status', 'minted')->count(),
        ];

        $cardsUploadedThisWeek = Card::where('upload_week', Carbon::now()->weekOfYear)
            ->where('upload_year', Carbon::now()->year)
            ->count();

        $mostActiveUsers = User::withCount('cards')
            ->orderBy('cards_count', 'desc')
            ->limit(5)
            ->get();

        $mostRareCards = Card::where('status', 'minted')
            ->orderBy('rarity', 'desc')
            ->with('user')
            ->limit(5)
            ->get();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalCards' => $totalCards,
                'cardsByStatus' => $cardsByStatus,
                'cardsUploadedThisWeek' => $cardsUploadedThisWeek,
            ],
            'mostActiveUsers' => $mostActiveUsers,
            'mostRareCards' => $mostRareCards,
            'isWeekend' => in_array(Carbon::now()->dayOfWeek, [0, 6]), // 0 = Sunday, 6 = Saturday
        ]);
    }

    /**
     * Display all users.
     */
    public function users()
    {
        $users = User::withCount(['cards', 'ownedCards'])
            ->latest()
            ->get();

        return Inertia::render('admin/Users', [
            'users' => $users,
        ]);
    }

    /**
     * Show/edit user details.
     */
    public function editUser(User $user)
    {
        $user->load(['cards', 'ownedCards.card']);

        return Inertia::render('admin/EditUser', [
            'user' => $user,
        ]);
    }

    /**
     * Update user details.
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'invitation_limit' => 'required|integer|min:0',
            'invitation_count' => 'required|integer|min:0|max:' . $request->invitation_limit,
            'is_admin' => 'boolean',
        ]);

        // Don't allow removing admin status from self
        if (Auth::id() === $user->id && $user->is_admin && !$request->is_admin) {
            return back()->with('error', 'You cannot remove admin status from yourself.');
        }

        $user->update([
            'name' => $request->name,
            'invitation_limit' => $request->invitation_limit,
            'invitation_count' => $request->invitation_count,
            'is_admin' => $request->is_admin,
        ]);

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Display all cards.
     */
    public function cards()
    {
        $cards = Card::with('user')
            ->latest()
            ->get();

        return Inertia::render('admin/Cards', [
            'cards' => $cards,
        ]);
    }

    /**
     * Change card status (e.g., force into voting or mint state).
     */
    public function updateCardStatus(Request $request, Card $card)
    {
        $request->validate([
            'status' => 'required|in:draft,voting,minted',
        ]);

        $card->status = $request->status;
        $card->save();

        return back()->with('success', 'Card status updated successfully.');
    }

    /**
     * Force start the minting process.
     */
    public function forceMint()
    {
        // This will redirect to the CardController mintCards method
        return app(CardController::class)->mintCards();
    }

    /**
     * System settings.
     */
    public function settings()
    {
        return Inertia::render('admin/Settings');
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'default_invitation_limit' => 'integer|min:0',
        ]);

        // In a real app, you would save these settings to a settings table
        // For now, we'll just return as if it's successful

        return back()->with('success', 'Settings updated successfully.');
    }
}
