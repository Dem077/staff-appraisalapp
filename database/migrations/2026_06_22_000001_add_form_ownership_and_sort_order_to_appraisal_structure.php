<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appraisal_form_categories', function (Blueprint $table) {
            $table->foreignId('appraisal_form_id')->nullable()->after('id')->constrained('appraisal_forms')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0)->after('type');
        });

        Schema::table('appraisal_form_key_behaviors', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('appraisal_form_category_id');
        });

        Schema::table('appraisal_form_questions', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('appraisal_form_key_behavior_id');
        });
    }

    public function down(): void
    {
        Schema::table('appraisal_form_questions', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('appraisal_form_key_behaviors', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('appraisal_form_categories', function (Blueprint $table) {
            $table->dropForeign(['appraisal_form_id']);
            $table->dropColumn(['appraisal_form_id', 'sort_order']);
        });
    }
};
