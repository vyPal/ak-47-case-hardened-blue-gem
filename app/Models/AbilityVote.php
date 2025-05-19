<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbilityVote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'ability_id',
        'user_id',
        'recommended',
        'voted',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recommended' => 'boolean',
            'voted' => 'boolean',
        ];
    }

    /**
     * Get the ability this vote belongs to
     */
    public function ability()
    {
        return $this->belongsTo(CardAbility::class, 'ability_id');
    }

    /**
     * Get the user who cast this vote
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include recommended abilities
     */
    public function scopeRecommended($query)
    {
        return $query->where('recommended', true);
    }

    /**
     * Scope a query to only include voted abilities
     */
    public function scopeVoted($query)
    {
        return $query->where('voted', true);
    }
}