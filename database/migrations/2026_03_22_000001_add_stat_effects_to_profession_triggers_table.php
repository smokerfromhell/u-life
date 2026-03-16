<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profession_triggers', function (Blueprint $table) {
            $table->text('stat_effects')->nullable()->after('notes');
            $table->text('description')->nullable()->after('stat_effects');
        });
    }

    public function down(): void
    {
        Schema::table('profession_triggers', function (Blueprint $table) {
            $table->dropColumn(['stat_effects', 'description']);
        });
    }
};
