<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'trade_id',
        'card_id',
        'user_id',
        'quantity',
        'serial_numbers',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'serial_numbers' => 'array',
        ];
    }

    /**
     * Get the trade this item belongs to
     */
    public function trade()
    {
        return $this->belongsTo(Trade::class);
    }

    /**
     * Get the card being traded
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the user offering this card
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}