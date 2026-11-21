<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Remises de cash par les livreurs au QG ────────────────────────────
        Schema::create('courier_cash_drops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount_millimes')->unsigned();
            $table->string('status', 20)->default('validated'); // validated | pending | rejected
            $table->string('reference', 50)->unique();          // ex: DROP-20261121-0001
            $table->text('notes')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        // ── Signalements d'incidents par les livreurs ─────────────────────────
        Schema::create('order_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('courier_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 50);                         // client_absent, adresse_introuvable, panne...
            $table->text('description');
            $table->string('photo')->nullable();
            $table->string('status', 20)->default('open');      // open | investigating | resolved
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_incidents');
        Schema::dropIfExists('courier_cash_drops');
    }
};
