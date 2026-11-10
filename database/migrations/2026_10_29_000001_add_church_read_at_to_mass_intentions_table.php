<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mass_intentions', function (Blueprint $table) {
            $table->timestamp('church_read_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mass_intentions', function (Blueprint $table) {
            $table->dropColumn('church_read_at');
        });
    }
};
