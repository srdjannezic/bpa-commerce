<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->nullable()->change();
        });

        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('session_key')->nullable()->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->foreignId('cart_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::table('carts')->insertUsing(['user_id'], DB::table('cart_items')->select('user_id')->distinct());
        DB::table('cart_items')->update(['cart_id' => DB::table('carts')
            ->select('id')->whereColumn('carts.user_id', 'cart_items.user_id')]);

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'product_id']);
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('cart_id')->nullable(false)->change();
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();
            $table->index(['cart_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['cart_id']);
            $table->dropIndex(['cart_id', 'id']);
            $table->dropColumn('cart_id');
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::table('cart_items')->update(['user_id' => DB::table('carts')
            ->select('user_id')->whereColumn('carts.id', 'cart_items.cart_id')]);
        DB::table('cart_items')->whereNull('user_id')->delete();

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropForeign(['cart_id']);
            $table->dropUnique(['cart_id', 'product_id']);
            $table->dropColumn('cart_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::dropIfExists('carts');

        DB::table('users')->whereNull('password')->update(['password' => '']);
        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->nullable(false)->change();
        });
    }
};
