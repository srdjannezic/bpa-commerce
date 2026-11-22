<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;

    /**
     * Cast the recorded unit price and quantity to integers.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['unit_price_minor' => 'integer', 'quantity' => 'integer'];
    }

    /**
     * Get the order that owns this line item.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the catalog product referenced by this line item.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
