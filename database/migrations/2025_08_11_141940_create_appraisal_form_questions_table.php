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
        Schema::create('appraisal_form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_form_key_behavior_id')
                ->constrained('appraisal_form_key_behaviors')
                ->onDelete('cascade');
            $table->string('behavioral_indicators');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_form_questions');
    }
};
