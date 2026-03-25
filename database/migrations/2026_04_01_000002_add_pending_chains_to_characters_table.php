<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add pending_chains field to track chains started but not completed across age groups.
     */
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->json('pending_chains')->nullable()->after('completed_event_chains');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('pending_chains');
        });
    }
};
