<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardAbility;
use App\Models\CardOwnership;
use App\Models\CardVote;
use App\Models\AbilityVote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CardController extends Controller
{
    /**
     * Display a listing of the user's cards.
     */
    public function index()
    {
        $ownedCardOwnerships = Auth::user()->ownedCards()
            ->with('card.abilities')
            ->latest()
            ->get();

        $cards = $ownedCardOwnerships->map(function ($ownership) {
            $card = $ownership->card;

            if (!$card) {
                return null;
            }
            $cardData = $card->toArray();

            $cardData['abilities'] = $card->abilities;

            if ($ownership->user_id === Auth::id()) {
                $cardData['serial_numbers'] = $ownership->serial_numbers;
            } else {
                $cardData['serial_numbers'] = [];
            }

            return $cardData;
        })->filter()->values();

        return Inertia::render('cards/Index', [
            'cards' => $cards,
            'uploadAllowed' => Card::isUploadAllowed() || Auth::user()->is_admin,
            'isWeekday' => Card::isWeekday(),
        ]);
    }

    /**
     * Show the form for creating a new card.
     */
    public function create()
    {
        // Only allow uploads Monday-Friday unless admin
        if (!Card::isUploadAllowed() && !Auth::user()->is_admin) {
            return redirect()->route('cards.index')
                ->with('error', 'Card uploads are only allowed Monday through Friday.');
        }

        return Inertia::render('cards/Create', [
            'isWeekday' => Card::isWeekday(),
        ]);
    }

    /**
     * Store a newly created card in storage.
     */
    public function store(Request $request)
    {
        // Only allow uploads Monday-Friday unless admin
        if (!Card::isUploadAllowed() && !Auth::user()->is_admin) {
            return redirect()->route('cards.index')
                ->with('error', 'Card uploads are only allowed Monday through Friday.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|max:15360', // 15MB max
        ]);

        // Store the image
        $imagePath = $request->file('image')->store('card-images', 'public');

        // Get current upload period
        $uploadPeriod = Card::getCurrentUploadPeriod();

        // Create the card
        $card = Auth::user()->cards()->create([
            'name' => $request->name,
            'description' => $request->description,
            'image_path' => $imagePath,
            'status' => 'draft',
            'upload_week' => $uploadPeriod['upload_week'],
            'upload_year' => $uploadPeriod['upload_year'],
        ]);

        return redirect()->route('cards.show', $card)
            ->with('success', 'Card created successfully.');
    }

    /**
     * Display the specified card.
     */
    public function show(Card $card)
    {
        $card->load(['abilities', 'user']);

        // Get the user's votes if the card is in voting state
        $userVotes = null;
        $abilityVotes = [];

        if ($card->isVoting()) {
            $userVotes = CardVote::where('card_id', $card->id)
                ->where('user_id', Auth::id())
                ->first();

            $abilityVotes = AbilityVote::where('user_id', Auth::id())
                ->whereIn('ability_id', $card->abilities->pluck('id'))
                ->get()
                ->keyBy('ability_id');
        }

        // Get card ownership details if the card is minted
        $ownershipInfo = null;
        if ($card->isMinted()) {
            $ownershipInfo = CardOwnership::where('card_id', $card->id)
                ->with('user')
                ->get()
                ->mapWithKeys(function ($ownership) {
                    return [$ownership->user->id => [
                        'user' => $ownership->user->only('id', 'name'),
                        'quantity' => $ownership->quantity,
                    ]];
                });
        }

        return Inertia::render('cards/Show', [
            'card' => $card,
            'userVotes' => $userVotes,
            'abilityVotes' => $abilityVotes,
            'ownershipInfo' => $ownershipInfo,
            'isOwner' => $card->user_id === Auth::id(),
            'isAdmin' => Auth::user()->is_admin,
        ]);
    }

    /**
     * Show the form for editing the specified card.
     */
    public function edit(Card $card)
    {
        // Only allow editing draft cards
        if (!$card->isDraft()) {
            return redirect()->route('cards.show', $card)
                ->with('error', 'This card cannot be edited anymore.');
        }

        // Only the owner or an admin can edit the card
        if ($card->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        return Inertia::render('cards/Edit', [
            'card' => $card,
        ]);
    }

    /**
     * Update the specified card in storage.
     */
    public function update(Request $request, Card $card)
    {
        // Only allow editing draft cards
        if (!$card->isDraft()) {
            return redirect()->route('cards.show', $card)
                ->with('error', 'This card cannot be edited anymore.');
        }

        // Only the owner or an admin can edit the card
        if ($card->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:15360', // 15MB max
        ]);

        $card->name = $request->name;
        $card->description = $request->description;

        // Update image if a new one was uploaded
        if ($request->hasFile('image')) {
            // Delete old image
            if ($card->image_path) {
                Storage::disk('public')->delete($card->image_path);
            }

            // Store new image
            $imagePath = $request->file('image')->store('card-images', 'public');
            $card->image_path = $imagePath;
        }

        $card->save();

        return redirect()->route('cards.show', $card)
            ->with('success', 'Card updated successfully.');
    }

    /**
     * Remove the specified card from storage.
     */
    public function destroy(Card $card)
    {
        // Only allow deleting draft cards
        if (!$card->isDraft()) {
            return redirect()->route('cards.show', $card)
                ->with('error', 'This card cannot be deleted anymore.');
        }

        // Only the owner or an admin can delete the card
        if ($card->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        // Delete the card image
        if ($card->image_path) {
            Storage::disk('public')->delete($card->image_path);
        }

        $card->delete();

        return redirect()->route('cards.index')
            ->with('success', 'Card deleted successfully.');
    }

    /**
     * Submit card for voting.
     */
    public function submitForVoting(Card $card)
    {
        // Only allow submitting draft cards
        if (!$card->isDraft()) {
            return redirect()->route('cards.show', $card)
                ->with('error', 'This card has already been submitted.');
        }

        // Only the owner or an admin can submit the card
        if ($card->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $card->status = 'voting';
        $card->save();

        return redirect()->route('cards.show', $card)
            ->with('success', 'Card submitted for voting.');
    }

    /**
     * Show the voting feed.
     */
    public function votingFeed()
    {
        // Get current day of week (0 = Sunday, 6 = Saturday)
        $dayOfWeek = Carbon::now()->dayOfWeek;

        // Check if it's weekend (Saturday or Sunday)
        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);

        // Get cards in voting state that the user hasn't voted on yet
        $votedCardIds = CardVote::where('user_id', Auth::id())->pluck('card_id');

        $card = Card::where('status', 'voting')
            ->whereNotIn('id', $votedCardIds)
            ->with(['abilities', 'user'])
            ->inRandomOrder()
            ->first();

        return Inertia::render('cards/VotingFeed', [
            'card' => $card,
            'isWeekend' => $isWeekend,
        ]);
    }

    /**
     * Submit a vote for a card.
     */
    public function vote(Request $request, Card $card)
    {
        // Validate the card is in voting state
        if (!$card->isVoting()) {
            return redirect()->route('cards.voting-feed')
                ->with('error', 'This card is not available for voting.');
        }

        // Check if user already voted for this card
        $existingVote = CardVote::where('card_id', $card->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($existingVote) {
            return redirect()->route('cards.voting-feed')
                ->with('error', 'You have already voted on this card.');
        }

        $request->validate([
            'recommended_rarity' => 'required|integer|min:1|max:10',
            'recommended_hp' => 'required|integer|min:1|max:100',
            'recommended_strength' => 'required|integer|min:1|max:100',
            'recommended_defense' => 'required|integer|min:1|max:100',
            'ability_recommendations' => 'nullable|array|max:2',
            'ability_recommendations.*.name' => 'required|string|max:255',
            'ability_recommendations.*.description' => 'nullable|string',
            'ability_votes' => 'nullable|array|max:3',
            'ability_votes.*' => 'required|integer|exists:card_abilities,id',
        ]);

        DB::transaction(function () use ($request, $card) {
            // Create the card vote
            CardVote::create([
                'card_id' => $card->id,
                'user_id' => Auth::id(),
                'recommended_rarity' => $request->recommended_rarity,
                'recommended_hp' => $request->recommended_hp,
                'recommended_strength' => $request->recommended_strength,
                'recommended_defense' => $request->recommended_defense,
            ]);

            // Process ability recommendations
            if ($request->has('ability_recommendations')) {
                foreach ($request->ability_recommendations as $abilityData) {
                    $ability = CardAbility::create([
                        'card_id' => $card->id,
                        'name' => $abilityData['name'],
                        'description' => $abilityData['description'] ?? null,
                        'votes_count' => 1, // Start with 1 vote from the recommender
                    ]);

                    // Record that this user recommended this ability
                    AbilityVote::create([
                        'ability_id' => $ability->id,
                        'user_id' => Auth::id(),
                        'recommended' => true,
                        'voted' => true,
                    ]);
                }
            }

            // Process ability votes
            if ($request->has('ability_votes')) {
                foreach ($request->ability_votes as $abilityId) {
                    $ability = CardAbility::find($abilityId);

                    if ($ability && $ability->card_id === $card->id) {
                        $ability->incrementVotesCount();

                        // Record that this user voted for this ability
                        AbilityVote::create([
                            'ability_id' => $ability->id,
                            'user_id' => Auth::id(),
                            'recommended' => false,
                            'voted' => true,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('cards.voting-feed')
            ->with('success', 'Your vote has been recorded.');
    }

    /**
     * Finalize voting and mint cards (admin only).
     */
    public function mintCards()
    {
        // Only admin can mint cards
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $votingCards = Card::where('status', 'voting')
            ->with(['votes', 'abilities'])
            ->get();

        foreach ($votingCards as $card) {
            // Skip cards with no votes
            if ($card->votes->isEmpty()) {
                continue;
            }

            // Calculate average values from votes
            $avgRarity = round($card->votes->avg('recommended_rarity'));
            $avgHp = round($card->votes->avg('recommended_hp'));
            $avgStrength = round($card->votes->avg('recommended_strength'));
            $avgDefense = round($card->votes->avg('recommended_defense'));

            // Update card stats
            $card->rarity = $avgRarity;
            $card->hp = $avgHp;
            $card->strength = $avgStrength;
            $card->defense = $avgDefense;

            // Select top abilities (max 3)
            $topAbilities = $card->abilities()
                ->orderBy('votes_count', 'desc')
                ->limit(3)
                ->get();

            // Mark top abilities as selected
            foreach ($topAbilities as $ability) {
                $ability->selected = true;
                $ability->save();
            }

            // Get the total number of users on the platform
            $totalUsers = User::count();

            // Calculate how many copies to mint based on rarity and user count
            // Rarity 10 (highest): ~1-2% of users will get a copy
            // Rarity 1 (lowest): ~80% of users will get a copy
            // Scale other rarities proportionally
            $percentOfUsers = 0.8 - (($avgRarity - 1) * 0.08); // 80% for rarity 1, down to 2% for rarity 10
            $baseCount = max(1, ceil($totalUsers * $percentOfUsers));

            // Add some randomness within reasonable bounds (±10% of calculated amount)
            $randomVariation = rand(-$baseCount * 0.1, $baseCount * 0.1);
            $mintCount = max(1, round($baseCount + $randomVariation));

            // Ensure highest rarity cards are truly rare
            if ($avgRarity >= 9) {
                $mintCount = min($mintCount, ceil($totalUsers * 0.02)); // Max 2% of users for very rare cards
            }

            // Update card status and minted count
            $card->status = 'minted';
            $card->minted_count = $mintCount;
            $card->save();

            // Get all users
            $allUsers = User::all();
            $serialNumber = 1;

            if ($allUsers->isNotEmpty()) {
                // Create a distribution pool with weights for each user
                $pool = [];
                $totalWeight = 0;

                foreach ($allUsers as $user) {
                    // Calculate user weight based on card collection
                    $avgCardRarity = $user->ownedCards()
                        ->join('cards', 'card_ownerships.card_id', '=', 'cards.id')
                        ->avg('rarity') ?: 0;

                    // Base weight - higher for users with lower average rarity
                    $weight = max(0.1, 11 - $avgCardRarity);

                    // Creator gets 50% higher chance (1.5x weight)
                    if ($user->id === $card->user_id) {
                        $weight *= 1.5;
                    }

                    $pool[] = [
                        'user' => $user,
                        'weight' => $weight
                    ];

                    $totalWeight += $weight;
                }

                // Keep track of which users received how many copies
                $distributedCopies = [];

                // Distribute all copies randomly but weighted
                for ($i = 0; $i < $mintCount; $i++) {
                    // Select a random user based on weights
                    $rand = mt_rand(1, 10000) / 10000 * $totalWeight;
                    $currentWeight = 0;
                    $selectedUser = null;

                    foreach ($pool as $entry) {
                        $currentWeight += $entry['weight'];
                        if ($rand <= $currentWeight) {
                            $selectedUser = $entry['user'];
                            break;
                        }
                    }

                    // If no user was selected (shouldn't happen), pick one randomly
                    if (!$selectedUser) {
                        $selectedUser = $allUsers->random();
                    }

                    // Add this card to the user's distributed copies
                    if (!isset($distributedCopies[$selectedUser->id])) {
                        $distributedCopies[$selectedUser->id] = [];
                    }

                    $distributedCopies[$selectedUser->id][] = $serialNumber++;
                }

                // Create ownership records for users who received copies
                foreach ($distributedCopies as $userId => $serialNumbers) {
                    CardOwnership::create([
                        'card_id' => $card->id,
                        'user_id' => $userId,
                        'quantity' => count($serialNumbers),
                        'serial_numbers' => $serialNumbers,
                    ]);
                }

                // Not every user will get a copy, and some might get multiple copies
                // This is by design as per the requirements
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Cards have been minted and distributed.');
    }
}
