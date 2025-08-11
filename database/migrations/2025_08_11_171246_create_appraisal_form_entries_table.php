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
        Schema::create('appraisal_form_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_assigned_to_staff_id')
                ->constrained('appraisal_form_assigned_to_staff')
                ->onDelete('cascade');
            $table->foreignId('question_id')
                ->constrained('appraisal_form_questions')
                ->onDelete('cascade');
            $table->integer('staff_score')->nullable();
            $table->integer('supervisor_score')->nullable();
            $table->text('supervisor_comment')->nullable();
            $table->boolean('hidden')->default(false)->comment('Indicates if the entry is hidden/NA to staff');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_form_entries');
    }
};
