<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Card extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'image_path',
        'status',
        'rarity',
        'hp',
        'strength',
        'defense',
        'balance_score',
        'upload_week',
        'upload_year',
        'minted_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rarity' => 'integer',
            'hp' => 'integer',
            'strength' => 'integer',
            'defense' => 'integer',
            'balance_score' => 'float',
            'upload_week' => 'integer',
            'upload_year' => 'integer',
            'minted_count' => 'integer',
        ];
    }

    /**
     * Get the user that uploaded the card
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the abilities associated with the card
     */
    public function abilities()
    {
        return $this->hasMany(CardAbility::class);
    }

    /**
     * Get the votes for this card
     */
    public function votes()
    {
        return $this->hasMany(CardVote::class);
    }

    /**
     * Get users who own this card
     */
    public function owners()
    {
        return $this->hasMany(CardOwnership::class);
    }

    /**
     * Check if the card is in draft state
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the card is in voting state
     */
    public function isVoting(): bool
    {
        return $this->status === 'voting';
    }

    /**
     * Check if the card is minted
     */
    public function isMinted(): bool
    {
        return $this->status === 'minted';
    }

    /**
     * Get the image URL
     */
    public function getImageUrl(): string
    {
        return Storage::url($this->image_path);
    }

    /**
     * Set the upload week and year based on current date
     */
    public static function getCurrentUploadPeriod(): array
    {
        $now = Carbon::now();
        return [
            'upload_week' => $now->weekOfYear,
            'upload_year' => $now->year,
        ];
    }

    /**
     * Check if uploads are currently allowed
     */
    public static function isUploadAllowed(): bool
    {
        // Always allow uploads in non-production environments
        if (!app()->environment('production')) {
            return true;
        }

        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
        
        // Allow uploads Monday through Friday (day 1-5)
        return $dayOfWeek >= 1 && $dayOfWeek <= 5;
    }

    /**
     * Check if it's currently a weekday
     */
    public static function isWeekday(): bool
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
        return $dayOfWeek >= 1 && $dayOfWeek <= 5;
    }
}