<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->string('role', 30)->default('customer')->after('phone'); // customer, partner_owner, partner_staff, courier, ops, admin, finance
            $table->string('status', 20)->default('active')->after('role'); // active, pending, suspended
            $table->string('otp_code', 10)->nullable()->after('status');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'status', 'otp_code', 'otp_expires_at']);
        });
    }
};

