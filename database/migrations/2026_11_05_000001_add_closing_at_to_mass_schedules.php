<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->dateTime('closing_at')->nullable()->after('mass_date');
        });
    }

    public function down(): void
    {
        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->dropColumn('closing_at');
        });
    }
};
