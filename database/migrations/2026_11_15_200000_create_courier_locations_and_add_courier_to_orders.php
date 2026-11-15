<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Positions des livreurs en temps réel ──────────────────────────────
        Schema::create('courier_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('heading')->nullable();          // direction en degrés (0-360)
            $table->float('speed')->nullable();            // vitesse en km/h ou m/s
            $table->unsignedTinyInteger('battery')->nullable(); // % batterie livreur
            $table->boolean('is_online')->default(true);
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamps();
        });

        // ── Ajout livreur et timestamps aux commandes ────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('courier_id')->nullable()->after('store_id')->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->timestamp('ready_at')->nullable()->after('accepted_at');
            $table->timestamp('picked_up_at')->nullable()->after('ready_at');
            $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropColumn(['courier_id', 'accepted_at', 'ready_at', 'picked_up_at', 'delivered_at']);
        });

        Schema::dropIfExists('courier_locations');
    }
};
