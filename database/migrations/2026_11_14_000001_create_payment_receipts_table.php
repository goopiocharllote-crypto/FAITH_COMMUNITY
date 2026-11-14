<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Receipt pictures are kept in the database (base64) because the hosting platform
        // does not keep uploaded files between requests.
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('mime', 50);
            $table->string('original_name', 150)->nullable();
            $table->unsignedInteger('size');
            $table->longText('data');
            $table->timestamps();

            $table->unique('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
