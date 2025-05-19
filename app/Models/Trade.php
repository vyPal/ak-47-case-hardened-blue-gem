<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'initiator_id',
        'recipient_id',
        'status',
        'message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Get the initiator user
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * Get the recipient user
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the trade items
     */
    public function items()
    {
        return $this->hasMany(TradeItem::class);
    }

    /**
     * Get the initiator's trade items
     */
    public function initiatorItems()
    {
        return $this->hasMany(TradeItem::class)
            ->where('user_id', $this->initiator_id);
    }

    /**
     * Get the recipient's trade items
     */
    public function recipientItems()
    {
        return $this->hasMany(TradeItem::class)
            ->where('user_id', $this->recipient_id);
    }

    /**
     * Check if the trade is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the trade is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if the trade is declined
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Check if the trade is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Accept the trade
     */
    public function accept(): void
    {
        $this->status = 'accepted';
        $this->save();
    }

    /**
     * Decline the trade
     */
    public function decline(): void
    {
        $this->status = 'declined';
        $this->save();
    }

    /**
     * Cancel the trade
     */
    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }
}