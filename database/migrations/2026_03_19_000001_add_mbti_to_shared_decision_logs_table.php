<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shared_decision_logs', function (Blueprint $table) {
            // Add missing columns (user_name might already exist from previous migration)
            if (!Schema::hasColumn('shared_decision_logs', 'event_title')) {
                $table->string('event_title', 255)->nullable()->after('choice_index');
            }
            if (!Schema::hasColumn('shared_decision_logs', 'choice_text')) {
                $table->string('choice_text', 500)->nullable()->after('event_title');
            }
            if (!Schema::hasColumn('shared_decision_logs', 'mbti')) {
                $table->string('mbti', 4)->nullable()->after('choice_text');
            }
            if (!Schema::hasColumn('shared_decision_logs', 'effects')) {
                $table->longText('effects')->nullable()->after('mbti');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shared_decision_logs', function (Blueprint $table) {
            $table->dropColumn(['event_title', 'choice_text', 'mbti', 'effects']);
        });
    }
};
