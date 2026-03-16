<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add profession field to shared_decision_logs table (at the end)
        Schema::table('shared_decision_logs', function (Blueprint $table) {
            $table->string('profession')->nullable()->after('data');
        });

        // Add profession fields to decision_logs table
        Schema::table('decision_logs', function (Blueprint $table) {
            $table->string('profession')->nullable()->after('age_group');
            $table->string('before_profession')->nullable()->after('before_career_level');
            $table->string('after_profession')->nullable()->after('after_career_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shared_decision_logs', function (Blueprint $table) {
            $table->dropColumn(['profession']);
        });

        Schema::table('decision_logs', function (Blueprint $table) {
            $table->dropColumn(['profession', 'before_profession', 'after_profession']);
        });
    }
};
