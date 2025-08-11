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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('emp_no')->nullable();
            $table->string('gender', 1)->nullable();
            $table->string('designation')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->integer('department_id')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('location_id')->nullable();
            $table->string('nid')->nullable();
            $table->integer('supervisor_id')->nullable();
            $table->date('joined_date')->nullable();
            $table->boolean('is_annual_applicable')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->string('external_id')->nullable();
            $table->string('theme')->nullable();
            $table->string('theme_color')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
