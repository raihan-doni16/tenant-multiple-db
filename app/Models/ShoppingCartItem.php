<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

class ShoppingCartItem extends Model
{
    use HasFactory;
    use TenantConnection;

    protected $fillable = [
        'shopping_cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'metadata',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(ShoppingCart::class, 'shopping_cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function subtotal(): float
    {
        return $this->quantity * (float) $this->unit_price;
    }
}
