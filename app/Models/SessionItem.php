<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_session_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(CustomerSession::class, 'customer_session_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}