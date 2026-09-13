<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'started_at',
        'closed_at',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function items()
    {
        return $this->hasMany(SessionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}