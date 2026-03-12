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
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->string('required_stat')->nullable()->after('weight');
            $table->integer('stat_threshold')->nullable()->after('required_stat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->dropColumn(['required_stat', 'stat_threshold']);
        });
    }
};

