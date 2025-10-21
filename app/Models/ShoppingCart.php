<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

class ShoppingCart extends Model
{
    use HasFactory;
    use TenantConnection;

    protected $fillable = [
        'user_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ShoppingCartItem::class);
    }

    public function subtotal(): float
    {
        return $this->items->sum(fn (ShoppingCartItem $item) => $item->subtotal());
    }
}
