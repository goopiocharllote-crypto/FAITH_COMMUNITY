<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->dropColumn('max_intentions');
        });
    }

    public function down(): void
    {
        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_intentions')->default(50);
        });
    }
};
