<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->text('outcome')->nullable()->change();
        });
        
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->text('outcome')->nullable()->change();
        });
        
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->text('outcome')->nullable()->change();
        });
        
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->text('outcome')->nullable()->change();
        });
        
        Schema::table('stat_trigger_conditions', function (Blueprint $table) {
            $table->text('outcome')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('daily_events', function (Blueprint $table) {
            $table->text('outcome')->change();
        });
        
        Schema::table('cultural_events', function (Blueprint $table) {
            $table->text('outcome')->change();
        });
        
        Schema::table('profession_path_events', function (Blueprint $table) {
            $table->text('outcome')->change();
        });
        
        Schema::table('age_specific_events', function (Blueprint $table) {
            $table->text('outcome')->change();
        });
        
        Schema::table('stat_trigger_conditions', function (Blueprint $table) {
            $table->text('outcome')->change();
        });
    }
};
