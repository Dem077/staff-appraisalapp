<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appraisal_form_categories', function (Blueprint $table) {
            $table->string('form_type')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('appraisal_form_categories', function (Blueprint $table) {
            $table->dropColumn('form_type');
        });
    }
};
