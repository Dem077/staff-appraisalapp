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
        Schema::create('hod_form_assignees', function (Blueprint $table) {
            $table->id();
            $table->enum('assignee_type', ['manager', 'co-worker' , 'subordinate'])
                ->comment('Type of the assignee, can be manager, co-worker, or subordinate');
            $table->foreignId('assignee_id')
                ->constrained('staff')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the assigned staff member');
            $table->string('assignee_comment')->nullable();
            $table->foreignId('forms_assigned_to_hod_id')
                ->constrained('forms_assigned_to_hods')
                ->onDelete('cascade')
                ->comment('Foreign key referencing the forms assigned to HOD');
            
            $table->enum('status', ['pending_staff_appraisal', 'complete'])->default('pending_staff_appraisal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hod_formassignees');
    }
};
