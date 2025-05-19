<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardOwnership extends Model
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
     * Get the card that is owned
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the user who owns the card
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Add a serial number to the ownership
     */
    public function addSerialNumber(int $serialNumber): void
    {
        $serialNumbers = $this->serial_numbers ?: [];
        $serialNumbers[] = $serialNumber;
        
        $this->serial_numbers = $serialNumbers;
        $this->quantity = count($serialNumbers);
        $this->save();
    }

    /**
     * Remove a serial number from the ownership
     */
    public function removeSerialNumber(int $serialNumber): void
    {
        $serialNumbers = $this->serial_numbers ?: [];
        $serialNumbers = array_diff($serialNumbers, [$serialNumber]);
        
        $this->serial_numbers = array_values($serialNumbers);
        $this->quantity = count($serialNumbers);
        
        if ($this->quantity <= 0) {
            $this->delete();
        } else {
            $this->save();
        }
    }
}