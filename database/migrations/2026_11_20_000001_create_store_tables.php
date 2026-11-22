<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the product, cart item, order and order item tables.
     */
    public function up(): void
    {
        Schema::create('products', /** Define the initial product catalog and inventory. */ function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->text('description');
            $table->unsignedInteger('price_minor');
            $table->unsignedInteger('stock');
            $table->unsignedBigInteger('revision')->default(1);
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'id']);
        });

        Schema::create('cart_items', /** Link the initial cart items to users and products. */ function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('orders', /** Store recipient, delivery and payment details for each order. */ function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('recipient_name', 120);
            $table->string('recipient_email');
            $table->string('phone', 30);
            $table->string('address', 150);
            $table->string('city', 100);
            $table->string('postal_code', 15);
            $table->unsignedBigInteger('total_minor');
            $table->string('payment_method', 20)->default('cod');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'id']);
            $table->index(['payment_status', 'id']);
        });

        Schema::create('order_items', /** Store the product name, price and quantity at purchase time. */ function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 120);
            $table->unsignedInteger('unit_price_minor');
            $table->unsignedSmallInteger('quantity');
            $table->unique(['order_id', 'product_id']);
        });
    }

    /**
     * Drop the store tables in dependency order.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('products');
    }
};
