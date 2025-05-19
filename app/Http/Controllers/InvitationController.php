<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class InvitationController extends Controller
{
    /**
     * Display a listing of the user's invitations.
     */
    public function index()
    {
        $user = Auth::user();
        $invitations = $user->invitations()
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->get();

        return Inertia::render('invitations/Index', [
            'invitations' => $invitations,
            'availableInvitations' => $user->invitation_count,
            'invitationLimit' => $user->invitation_limit,
        ]);
    }

    /**
     * Generate a new invitation.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->invitation_count <= 0) {
            return back()->with('error', 'You have no available invitations.');
        }

        $token = Invitation::generateToken();
        $expiresAt = Carbon::now()->addDays(7);

        $invitation = $user->invitations()->create([
            'token' => $token,
            'expires_at' => $expiresAt,
            'used' => false,
        ]);

        $user->decrementInvitationCount();

        return back()->with('success', 'Invitation created successfully.');
    }

    /**
     * Show the invitation registration form.
     */
    public function showRegistrationForm($token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            abort(404, 'Invalid or expired invitation.');
        }

        return Inertia::render('auth/RegisterInvite', [
            'token' => $token,
        ]);
    }

    /**
     * Register a new user using an invitation.
     */
    public function register(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            abort(404, 'Invalid or expired invitation.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'invitation_count' => 0,
            'invitation_limit' => 3,
        ]);

        $invitation->markAsUsed();

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Admin can increase a user's invitation limit.
     */
    public function increaseLimit(Request $request, User $user)
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $user->invitation_limit += $request->amount;
        $user->invitation_count += $request->amount;
        $user->save();

        return back()->with('success', "Increased {$user->name}'s invitation limit by {$request->amount}.");
    }

    /**
     * Revoke an invitation.
     */
    public function destroy(Invitation $invitation)
    {
        $user = Auth::user();

        if ($invitation->user_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        if (!$invitation->used) {
            $invitation->user->increment('invitation_count');
        }

        $invitation->delete();

        return back()->with('success', 'Invitation revoked successfully.');
    }
}
