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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du restaurant
            $table->string('description')->nullable(); // Description
            $table->string('address'); // Adresse
            $table->string('image')->nullable(); // Image du restaurant
            $table->boolean('is_open')->default(true); // Ouvert ou fermé
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};