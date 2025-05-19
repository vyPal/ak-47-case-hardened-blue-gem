<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Invitation;
use App\Models\CardOwnership;
use App\Models\Card;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'invitation_count',
        'invitation_limit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get trades initiated by this user
     */
    public function initiatedTrades()
    {
        return $this->hasMany(Trade::class, 'initiator_id');
    }

    /**
     * Get trades received by this user
     */
    public function receivedTrades()
    {
        return $this->hasMany(Trade::class, 'recipient_id');
    }

    /**
     * Check if the user has available invitations
     */
    public function hasAvailableInvitations(): bool
    {
        return $this->invitation_count > 0;
    }

    /**
     * Decrement the invitation count
     */
    public function decrementInvitationCount(): void
    {
        if ($this->invitation_count > 0) {
            $this->decrement('invitation_count');
        }
    }

    /**
     * Get the invitations created by this user
     */
    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * Get the cards uploaded by this user
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get the cards owned by this user
     */
    public function ownedCards()
    {
        return $this->hasMany(CardOwnership::class);
    }
}
