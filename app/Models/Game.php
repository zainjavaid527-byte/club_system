<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_session_id',
        'game_type',
        'rate',
        'played_at',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'played_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(CustomerSession::class, 'customer_session_id');
    }
}