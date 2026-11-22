<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create product categories and link products to them.
     */
    public function up(): void
    {
        Schema::create('categories', /** Store categories with unique names. */ function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80)->unique();
            $table->timestamps();
        });

        Schema::table('products', /** Allow products to reference a category. */ function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->index(['category_id', 'is_active', 'id']);
        });
    }

    /**
     * Remove the product category link and drop the category table.
     */
    public function down(): void
    {
        Schema::table('products', /** Remove the product reference before dropping categories. */ function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id', 'is_active', 'id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
