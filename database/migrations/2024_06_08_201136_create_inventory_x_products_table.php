<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_x_products', function (Blueprint $table) {
            $table->integer('quantity');
            $table->boolean('available');
            $table->foreignId('product_id')->constrained();
            $table->foreignId('inventory_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_x_products');
    }
};
