<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardOwnership;
use App\Models\Trade;
use App\Models\TradeItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TradeController extends Controller
{
    /**
     * Display a listing of the trades.
     */
    public function index()
    {
        $user = Auth::user();

        $initiatedTrades = Trade::where('initiator_id', $user->id)
            ->with(['recipient', 'items.card'])
            ->latest()
            ->get();

        $receivedTrades = Trade::where('recipient_id', $user->id)
            ->with(['initiator', 'items.card'])
            ->latest()
            ->get();

        return Inertia::render('trades/Index', [
            'initiatedTrades' => $initiatedTrades,
            'receivedTrades' => $receivedTrades,
        ]);
    }

    /**
     * Show the form for creating a new trade.
     */
    public function create(User $recipient)
    {
        // Get all users except the current user
        $users = User::where('id', '!=', Auth::id())->get(['id', 'name']);

        // Get the current user's card ownerships
        $myCards = CardOwnership::where('user_id', Auth::id())
            ->with('card')
            ->get();

        // Get the recipient's card ownerships if specified
        $recipientCards = null;
        if ($recipient->exists) {
            $recipientCards = CardOwnership::where('user_id', $recipient->id)
                ->with('card')
                ->get();
        }

        return Inertia::render('trades/Create', [
            'users' => $users,
            'myCards' => $myCards,
            'recipient' => $recipient->exists ? $recipient : null,
            'recipientCards' => $recipientCards,
        ]);
    }

    /**
     * Store a newly created trade in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:500',
            'my_cards' => 'required|array|min:1',
            'my_cards.*.card_id' => 'required|exists:cards,id',
            'my_cards.*.serial_numbers' => 'required|array|min:1',
            'recipient_cards' => 'nullable|array',
            'recipient_cards.*.card_id' => 'required|exists:cards,id',
            'recipient_cards.*.serial_numbers' => 'required|array|min:1',
        ]);

        // Check that the recipient is not the current user
        if ($request->recipient_id == Auth::id()) {
            return back()->with('error', 'You cannot trade with yourself.');
        }

        // Check that the current user owns all the offered cards
        foreach ($request->my_cards as $cardData) {
            $ownership = CardOwnership::where('user_id', Auth::id())
                ->where('card_id', $cardData['card_id'])
                ->first();

            if (!$ownership) {
                return back()->with('error', 'You do not own one or more of the offered cards.');
            }

            // Check that the user owns all the serial numbers they're offering
            foreach ($cardData['serial_numbers'] as $serialNumber) {
                if (!in_array($serialNumber, $ownership->serial_numbers)) {
                    return back()->with('error', 'You do not own one or more of the offered serial numbers.');
                }
            }
        }

        // Check that the recipient owns all the requested cards
        if ($request->has('recipient_cards')) {
            foreach ($request->recipient_cards as $cardData) {
                $ownership = CardOwnership::where('user_id', $request->recipient_id)
                    ->where('card_id', $cardData['card_id'])
                    ->first();

                if (!$ownership) {
                    return back()->with('error', 'The recipient does not own one or more of the requested cards.');
                }

                // Check that the recipient owns all the serial numbers being requested
                foreach ($cardData['serial_numbers'] as $serialNumber) {
                    if (!in_array($serialNumber, $ownership->serial_numbers)) {
                        return back()->with('error', 'The recipient does not own one or more of the requested serial numbers.');
                    }
                }
            }
        }

        // Create the trade
        DB::transaction(function () use ($request) {
            $trade = Trade::create([
                'initiator_id' => Auth::id(),
                'recipient_id' => $request->recipient_id,
                'status' => 'pending',
                'message' => $request->message,
            ]);

            // Add initiator's cards to the trade
            foreach ($request->my_cards as $cardData) {
                TradeItem::create([
                    'trade_id' => $trade->id,
                    'card_id' => $cardData['card_id'],
                    'user_id' => Auth::id(),
                    'quantity' => count($cardData['serial_numbers']),
                    'serial_numbers' => $cardData['serial_numbers'],
                ]);
            }

            // Add recipient's cards to the trade
            if ($request->has('recipient_cards')) {
                foreach ($request->recipient_cards as $cardData) {
                    TradeItem::create([
                        'trade_id' => $trade->id,
                        'card_id' => $cardData['card_id'],
                        'user_id' => $request->recipient_id,
                        'quantity' => count($cardData['serial_numbers']),
                        'serial_numbers' => $cardData['serial_numbers'],
                    ]);
                }
            }
        });

        return redirect()->route('trades.index')
            ->with('success', 'Trade offer sent successfully.');
    }

    /**
     * Display the specified trade.
     */
    public function show(Trade $trade)
    {
        // Check that the user is involved in the trade
        if ($trade->initiator_id !== Auth::id() && $trade->recipient_id !== Auth::id()) {
            abort(403);
        }

        $trade->load(['initiator', 'recipient', 'items.card']);

        // Group items by user
        $initiatorItems = $trade->items->where('user_id', $trade->initiator_id);
        $recipientItems = $trade->items->where('user_id', $trade->recipient_id);

        return Inertia::render('trades/Show', [
            'trade' => $trade,
            'initiatorItems' => $initiatorItems,
            'recipientItems' => $recipientItems,
            'isInitiator' => $trade->initiator_id === Auth::id(),
            'isRecipient' => $trade->recipient_id === Auth::id(),
        ]);
    }

    /**
     * Accept a trade.
     */
    public function accept(Trade $trade)
    {
        // Check that the user is the recipient of the trade
        if ($trade->recipient_id !== Auth::id()) {
            abort(403);
        }

        // Check that the trade is still pending
        if (!$trade->isPending()) {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'This trade is no longer pending.');
        }

        // Verify all parties still own the cards
        $valid = $this->validateTradeItems($trade);
        if (!$valid) {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'One or more cards in this trade are no longer available.');
        }

        // Execute the trade
        DB::transaction(function () use ($trade) {
            // Process each trade item
            foreach ($trade->items as $item) {
                $fromOwnership = CardOwnership::where('user_id', $item->user_id)
                    ->where('card_id', $item->card_id)
                    ->first();

                // Determine recipient based on who's giving the card
                $recipientId = ($item->user_id === $trade->initiator_id)
                    ? $trade->recipient_id
                    : $trade->initiator_id;

                // Find or create recipient's ownership record
                $toOwnership = CardOwnership::firstOrNew([
                    'user_id' => $recipientId,
                    'card_id' => $item->card_id,
                ], [
                    'quantity' => 0,
                    'serial_numbers' => [],
                ]);

                // Remove the serial numbers from the sender
                $fromSerialNumbers = $fromOwnership->serial_numbers;
                $fromOwnership->serial_numbers = array_values(array_diff($fromSerialNumbers, $item->serial_numbers));
                $fromOwnership->quantity = count($fromOwnership->serial_numbers);

                // Add the serial numbers to the recipient
                $toSerialNumbers = $toOwnership->serial_numbers ?: [];
                $toOwnership->serial_numbers = array_values(array_merge($toSerialNumbers, $item->serial_numbers));
                $toOwnership->quantity = count($toOwnership->serial_numbers);

                // Save changes
                if ($fromOwnership->quantity > 0) {
                    $fromOwnership->save();
                } else {
                    $fromOwnership->delete();
                }

                $toOwnership->save();
            }

            // Update trade status
            $trade->accept();
        });

        return redirect()->route('trades.show', $trade)
            ->with('success', 'Trade accepted successfully.');
    }

    /**
     * Decline a trade.
     */
    public function decline(Trade $trade)
    {
        // Check that the user is the recipient of the trade
        if ($trade->recipient_id !== Auth::id()) {
            abort(403);
        }

        // Check that the trade is still pending
        if (!$trade->isPending()) {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'This trade is no longer pending.');
        }

        // Update trade status
        $trade->decline();

        return redirect()->route('trades.index')
            ->with('success', 'Trade declined.');
    }

    /**
     * Cancel a trade.
     */
    public function cancel(Trade $trade)
    {
        // Check that the user is the initiator of the trade
        if ($trade->initiator_id !== Auth::id()) {
            abort(403);
        }

        // Check that the trade is still pending
        if (!$trade->isPending()) {
            return redirect()->route('trades.show', $trade)
                ->with('error', 'This trade is no longer pending.');
        }

        // Update trade status
        $trade->cancel();

        return redirect()->route('trades.index')
            ->with('success', 'Trade cancelled.');
    }

    /**
     * Validate that all cards in the trade are still available.
     */
    private function validateTradeItems(Trade $trade): bool
    {
        foreach ($trade->items as $item) {
            $ownership = CardOwnership::where('user_id', $item->user_id)
                ->where('card_id', $item->card_id)
                ->first();

            if (!$ownership) {
                return false;
            }

            // Check all serial numbers are still available
            foreach ($item->serial_numbers as $serialNumber) {
                if (!in_array($serialNumber, $ownership->serial_numbers)) {
                    return false;
                }
            }
        }

        return true;
    }
}
