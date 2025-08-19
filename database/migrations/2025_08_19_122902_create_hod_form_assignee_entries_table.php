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
        Schema::create('hod_form_assignee_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hod_form_assignee_id')
                ->constrained('hod_form_assignees')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the HOD form assignee');
            $table->foreignId('question_id')
                ->constrained('appraisal_form_questions')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the appraisal form question');
            $table->string('score')->nullable();
            $table->string('comment')->nullable();
            $table->boolean('hidden')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hod_form_assignee_entries');
    }
};
