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
        Schema::create('forms_assigned_to_hods', function (Blueprint $table) {
            $table->id();
            $table->date('assigned_date');
            $table->foreignId('appraisal_form_id')
                ->constrained('appraisal_forms')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the appraisal form');
            $table->foreignId('hod_id')
                ->constrained('staff')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the head of department');
            $table->string('hod_comment')->nullable();
            $table->string('hr_comment')->nullable();
            $table->string('supervisor_comment')->nullable();
            $table->enum('status', ['pending_staff_appraisal', 'pending_assignee_appraisal','hr_comment', 'complete'])->default('pending_staff_appraisal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms_assigned_to_hods');
    }
};
