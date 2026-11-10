<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mass_intentions', function (Blueprint $table) {
            $table->foreignId('schedule_id')
                ->nullable()
                ->after('user_id')
                ->constrained('mass_schedules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mass_intentions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('schedule_id');
        });
    }
};
