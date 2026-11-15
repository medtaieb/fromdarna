<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Panier temporaire (session ou utilisateur) ────────────────────
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();      // identifie le panier anonyme
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('options_json')->nullable();         // options choisies (JSON)
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ── Commandes ─────────────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->unique();                  // ex: FD-20261115-0001
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('zone_id')->constrained()->restrictOnDelete();
            $table->string('status', 30)->default('PLACED'); // PLACED | ACCEPTED | READY | PICKED_UP | DELIVERED | CANCELLED | REJECTED
            $table->string('payment_method', 20)->default('cash'); // cash | d17 | card
            $table->string('payment_status', 20)->default('pending'); // pending | paid | refunded
            // Montants en millimes
            $table->bigInteger('subtotal_millimes')->unsigned()->default(0);
            $table->bigInteger('delivery_fee_millimes')->unsigned()->default(0);
            $table->bigInteger('total_millimes')->unsigned()->default(0);
            // Livraison
            $table->string('delivery_landmark')->nullable();
            $table->string('delivery_city', 80)->nullable();
            $table->string('delivery_building', 50)->nullable();
            $table->string('delivery_floor', 20)->nullable();
            $table->string('delivery_door', 20)->nullable();
            $table->string('delivery_phone', 30)->nullable();
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            // Code de confirmation livraison
            $table->string('delivery_code', 10)->nullable();
            // Notes
            $table->text('customer_note')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->timestamps();
        });

        // ── Lignes de commande ────────────────────────────────────────────
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');                  // snapshot nom au moment de la commande
            $table->bigInteger('unit_price_millimes')->unsigned();
            $table->unsignedInteger('quantity')->default(1);
            $table->bigInteger('line_total_millimes')->unsigned();
            $table->text('options_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
    }
};
