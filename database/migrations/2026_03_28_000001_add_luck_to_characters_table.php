<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->integer('luck')->default(50)->after('happiness');
            $table->integer('karma')->default(0)->after('luck');
            $table->json('luck_history')->nullable()->after('karma');
            $table->timestamp('last_luck_event')->nullable()->after('luck_history');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['luck', 'karma', 'luck_history', 'last_luck_event']);
        });
    }
};
