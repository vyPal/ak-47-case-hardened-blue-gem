<?php

namespace App\Http\Controllers;

use App\Models\CardOwnership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Get users with card count and average rarity
        $users = $query->withCount('ownedCards')
            ->with(['ownedCards' => function ($query) {
                $query->join('cards', 'card_ownerships.card_id', '=', 'cards.id')
                    ->select('cards.rarity', 'card_ownerships.user_id')
                    ->whereNotNull('cards.rarity');
            }]);

        // Apply sorting
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'collection':
                    $users->orderBy('owned_cards_count', 'desc');
                    break;
                case 'newest':
                    $users->orderBy('created_at', 'desc');
                    break;
                // For rarity we'll sort after the query
                default:
                    $users->orderBy('name', 'asc');
            }
        } else {
            $users->orderBy('name', 'asc');
        }

        $users = $users->paginate(20)
            ->through(function ($user) {
                // Calculate average card rarity for display
                $avgRarity = $user->ownedCards->avg('rarity');

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'is_admin' => $user->is_admin,
                    'owned_cards_count' => $user->owned_cards_count,
                    'avg_rarity' => $avgRarity ? round($avgRarity, 1) : null,
                    'created_at' => $user->created_at,
                ];
            });

        // Manual sort by average rarity if needed
        if ($request->sort === 'rarity') {
            $users->setCollection($users->collection->sortByDesc('avg_rarity'));
        }

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'sort']),
        ]);
    }

    /**
     * Display the specified user's profile and collection.
     */
    public function show(User $user)
    {
        // Check if the requested user exists
        if (!$user) {
            return redirect()->route('users.index')
                ->with('error', 'User not found.');
        }

        // Get the user's cards with collection stats
        $cardOwnerships = CardOwnership::where('user_id', $user->id)
            ->with(['card'])
            ->get()
            ->map(function ($ownership) use ($user) {
                return [
                    'id' => $ownership->id,
                    'card_id' => $ownership->card_id,
                    'card' => [
                        'id' => $ownership->card->id,
                        'name' => $ownership->card->name,
                        'description' => $ownership->card->description,
                        'image_path' => $ownership->card->image_path,
                        'rarity' => $ownership->card->rarity,
                        'hp' => $ownership->card->hp,
                        'strength' => $ownership->card->strength,
                        'defense' => $ownership->card->defense,
                        'created_by' => $ownership->card->user_id === $user->id,
                    ],
                    'quantity' => $ownership->quantity,
                    'serial_numbers' => $ownership->serial_numbers,
                ];
            });

        // Get collection stats
        $totalCards = $cardOwnerships->count();
        $avgRarity = $cardOwnerships->average(function ($ownership) {
            return $ownership['card']['rarity'] ?? 0;
        });
        $rarityDistribution = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        foreach ($cardOwnerships as $ownership) {
            $rarity = $ownership['card']['rarity'] ?? 0;
            if ($rarity >= 1 && $rarity <= 10) {
                $rarityDistribution[$rarity - 1]++;
            }
        }

        // Check if the current user can initiate trade with this user
        $canTrade = Auth::check() && Auth::id() !== $user->id;

        return Inertia::render('users/Show', [
            'profileUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at->format('M d, Y'),
                'member_since' => $user->created_at->diffForHumans(),
            ],
            'collection' => [
                'cards' => $cardOwnerships,
                'createdCards' => $user->cards()->get(),
                'total_cards' => $totalCards,
                'avg_rarity' => round($avgRarity, 1),
                'rarity_distribution' => $rarityDistribution,
            ],
            'canTrade' => $canTrade,
        ]);
    }
}
