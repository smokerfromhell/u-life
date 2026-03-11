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
        Schema::table('characters', function (Blueprint $table) {
            // Remove the single last_redraw_date column
            $table->dropColumn('last_redraw_date');
            
            // Add separate redraw date columns for each event type
            $table->date('last_redraw_date_daily')->nullable()->after('shown_event_ids');
            $table->date('last_redraw_date_cultural')->nullable()->after('last_redraw_date_daily');
            $table->date('last_redraw_date_age_specific')->nullable()->after('last_redraw_date_cultural');
            $table->date('last_redraw_date_profession')->nullable()->after('last_redraw_date_age_specific');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('last_redraw_date_daily');
            $table->dropColumn('last_redraw_date_cultural');
            $table->dropColumn('last_redraw_date_age_specific');
            $table->dropColumn('last_redraw_date_profession');
            
            // Restore the single column
            $table->date('last_redraw_date')->nullable();
        });
    }
};

