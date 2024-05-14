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
        Schema::create('geos', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->foreignId('type_geo_id')->constrained()->onDelete('cascade');
            $table->foreignId('geo_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geos');
    }
};
