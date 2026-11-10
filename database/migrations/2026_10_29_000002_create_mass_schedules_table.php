<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mass_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->dateTime('mass_date');
            $table->unsignedSmallInteger('max_intentions')->default(50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'mass_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_schedules');
    }
};
