<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardAbility extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'card_id',
        'name',
        'description',
        'votes_count',
        'selected',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'votes_count' => 'integer',
            'selected' => 'boolean',
        ];
    }

    /**
     * Get the card this ability belongs to
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the votes for this ability
     */
    public function votes()
    {
        return $this->hasMany(AbilityVote::class, 'ability_id');
    }

    /**
     * Increment the votes count
     */
    public function incrementVotesCount(): void
    {
        $this->increment('votes_count');
    }

    /**
     * Check if this ability is one of the top abilities for its card
     */
    public function isTopAbility(): bool
    {
        return $this->selected;
    }
}