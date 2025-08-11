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
        Schema::create('appraisal_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Name of the appraisal form');
            $table->text('description')->comment('Description of the appraisal form');
            $table->enum('type', ['mid-year', 'year-end'])->default('mid-year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_forms');
    }
};
