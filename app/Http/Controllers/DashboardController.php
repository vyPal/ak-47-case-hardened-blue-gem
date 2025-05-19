<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $stats = [
            'totalCards' => $user->ownedCards()->count(),
            'activeTrades' => $user->initiatedTrades()->where('status', 'pending')->count() +
                            $user->receivedTrades()->where('status', 'pending')->count(),
        ];

        // Get recent activity (cards uploaded, trades, etc.)
        $recentActivity = $user->cards()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'type' => 'card',
                    'title' => $card->name,
                    'description' => $this->getActivityDescription($card),
                    'image_path' => $card->image_path,
                    'date' => $card->updated_at,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'uploadAllowed' => Card::isUploadAllowed() || $user->is_admin,
            'isWeekday' => Card::isWeekday(),
        ]);
    }

    private function getActivityDescription($card): string
    {
        return match($card->status) {
            'minted' => 'Card minted',
            'voting' => 'Submitted for voting',
            default => 'Draft created',
        };
    }
}
