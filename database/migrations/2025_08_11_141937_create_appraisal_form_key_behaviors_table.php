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
        Schema::create('appraisal_form_key_behaviors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('appraisal_form_category_id')
                ->constrained('appraisal_form_categories')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_form_key_behaviors');
    }
};
