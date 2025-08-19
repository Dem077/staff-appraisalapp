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
        Schema::create('hod_form_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forms_assigned_to_hod_id')
                ->constrained('forms_assigned_to_hods')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the forms assigned to HOD');
            $table->foreignId('question_id')
                ->constrained('appraisal_form_questions')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the appraisal form question');
            $table->string('self_score')->nullable();
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
        Schema::dropIfExists('hod_form_entries');
    }
};
