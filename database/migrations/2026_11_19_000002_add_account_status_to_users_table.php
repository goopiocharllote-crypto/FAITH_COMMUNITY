<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Set when an administrator deactivates the account; null means active.
            $table->timestamp('deactivated_at')->nullable()->after('role');
            // Deleted accounts are soft deleted so their Mass Intentions, donations
            // and GCash payments stay in the parish records.
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('deactivated_at');
        });
    }
};
