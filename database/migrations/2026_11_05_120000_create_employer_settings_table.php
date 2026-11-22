<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('tagline', 180)->nullable();
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->string('website')->nullable();
            $table->text('about_company')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('full_address')->nullable();
            $table->json('social_links')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->string('language', 12)->default('en');
            $table->string('timezone', 80)->default('UTC');
            $table->json('notification_preferences')->nullable();
            $table->string('default_job_type')->default('Full-time');
            $table->unsignedSmallInteger('application_deadline_days')->default(30);
            $table->boolean('auto_close_jobs')->default(false);
            $table->unsignedInteger('application_alert_threshold')->nullable();
            $table->json('screening_questions')->nullable();
            $table->boolean('public_profile')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_settings');
    }
};
