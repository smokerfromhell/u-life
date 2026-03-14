<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            // Life Stats - dedicated columns for easy querying and display
            $table->integer('health')->default(100)->after('effective_stats'); // 0-100 scale
            $table->integer('happiness')->default(100)->after('health'); // 0-100 scale
            $table->integer('finance')->default(0)->after('happiness'); // Can be negative (debt)
            $table->string('relationship_status')->default('single')->after('finance'); // single, dating, engaged, married, divorced, widowed
            $table->string('career_level')->default('unemployed')->after('relationship_status'); // unemployed, entry, junior, senior, manager, executive, retired
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'health',
                'happiness',
                'finance',
                'relationship_status',
                'career_level',
            ]);
        });
    }
};
