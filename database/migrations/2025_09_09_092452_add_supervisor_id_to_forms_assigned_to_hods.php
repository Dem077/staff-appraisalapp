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
        Schema::table('forms_assigned_to_hods', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                ->constrained('staff')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms_assigned_to_hods', function (Blueprint $table) {

            $table->dropColumn('supervisor_id');
        });
    }
};
