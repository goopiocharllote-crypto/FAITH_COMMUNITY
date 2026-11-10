<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mass_intentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('intention_type', 50);
            $table->string('intention_for');
            $table->text('additional_message')->nullable();
            $table->dateTime('mass_date');
            $table->string('mobile', 10);
            $table->decimal('offering_amount', 10, 2);
            $table->string('status', 20)->default('Pending');
            $table->string('payment_status', 20)->default('Pending');
            $table->timestamps();

            $table->index(['user_id', 'mass_date']);
            $table->index(['status', 'mass_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_intentions');
    }
};
