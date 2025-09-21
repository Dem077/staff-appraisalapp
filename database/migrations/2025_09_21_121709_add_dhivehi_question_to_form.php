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
        Schema::table('appraisal_form_questions', function (Blueprint $table) {
            $table->text('dhivehi_behavioral_indicators')->nullable()->after('behavioral_indicators');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appraisal_form_questions', function (Blueprint $table) {
            $table->dropColumn('dhivehi_behavioral_indicators');
        });
    }
};
