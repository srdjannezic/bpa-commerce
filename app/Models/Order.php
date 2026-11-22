<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'recipient_name', 'recipient_email', 'phone', 'address', 'city',
        'postal_code', 'total_minor', 'payment_method', 'payment_status',
    ];

    /**
     * Cast the total, payment status and payment timestamp.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_minor' => 'integer',
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the cart used to place this order.
     *
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the customer associated with this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the administrator who confirmed payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Get the line items recorded when the order was placed.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
