<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('Pending')->after('payment_method');
        });

        // Donations the parish already verified were received.
        DB::table('donations')->where('status', 'Approved')->update(['payment_status' => 'Paid']);

        Schema::table('mass_intentions', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('Cash')->after('offering_amount');
        });

        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->string('livestream_url', 500)->nullable()->after('closing_at');
        });
    }

    public function down(): void
    {
        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->dropColumn('livestream_url');
        });

        Schema::table('mass_intentions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
