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
        Schema::table('appraisal_forms', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name')->comment('Indicates if the appraisal form is active');
            $table->enum('level', ['level_1', 'level_2', 'level_3' ,'probationary'])->default('level_1')->after('is_active')->comment('Indicates the level of the appraisal form');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appraisal_forms', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('level');
        });
    }
};
