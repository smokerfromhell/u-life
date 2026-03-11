<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE age_specific_events MODIFY COLUMN age_group ENUM('child', 'teen', 'adult', 'elder', 'old')");
        DB::statement("ALTER TABLE age_specific_events MODIFY COLUMN weight FLOAT");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE age_specific_events MODIFY COLUMN age_group ENUM('child', 'teen', 'adult', 'old')");
        DB::statement("ALTER TABLE age_specific_events MODIFY COLUMN weight INT");
    }
};

