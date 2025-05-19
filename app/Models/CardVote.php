<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardVote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'card_id',
        'user_id',
        'recommended_rarity',
        'recommended_hp',
        'recommended_strength',
        'recommended_defense',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recommended_rarity' => 'integer',
            'recommended_hp' => 'integer',
            'recommended_strength' => 'integer',
            'recommended_defense' => 'integer',
        ];
    }

    /**
     * Get the card this vote belongs to
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the user who cast this vote
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}