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
        Schema::table('appraisal_form_categories', function (Blueprint $table) {
            $table->enum('type', ['form_level_1', 'form_level_2','form_level_3' , 'form_probationary'])->default('form_level_1')->after('name')->comment('Category type to distinguish between different appraisal forms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appraisal_form_categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
