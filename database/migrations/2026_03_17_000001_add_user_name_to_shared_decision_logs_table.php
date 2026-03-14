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
        Schema::table('shared_decision_logs', function (Blueprint $table) {
            $table->string('user_name', 128)->nullable()->after('is_guest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shared_decision_logs', function (Blueprint $table) {
            $table->dropColumn('user_name');
        });
    }
};
