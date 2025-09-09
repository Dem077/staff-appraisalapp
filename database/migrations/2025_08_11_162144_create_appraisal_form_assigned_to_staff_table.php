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
        Schema::create('appraisal_form_assigned_to_staff', function (Blueprint $table) {
            $table->id();
            $table->date('assigned_date');
            $table->foreignId('appraisal_form_id')
                ->constrained('appraisal_forms')
                ->onDelete('cascade');
            $table->foreignId('staff_id')
                ->constrained('staff')
                ->onDelete('cascade');
            $table->foreignId('supervisor_id')
                ->constrained('staff')
                ->onDelete('cascade');
            $table->text('supervisor_comment')->nullable();
            $table->text('staff_comment')->nullable();
            $table->text('hr_comment')->nullable();
            $table->enum('status', ['pending_staff_appraisal', 'pending_supervisor_appraisal','hr_comment', 'complete'])->default('pending_staff_appraisal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_form_assigned_to_staff');
    }
};
