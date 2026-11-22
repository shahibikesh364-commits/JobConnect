<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('headline')->nullable();
            $table->longText('about_me')->nullable();
            $table->longText('career_objective')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('preferred_job_type')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('resume_original_name')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn([
                'user_id',
                'phone',
                'address',
                'date_of_birth',
                'profile_photo',
                'headline',
                'about_me',
                'career_objective',
                'experience_level',
                'employment_status',
                'preferred_job_type',
                'resume_path',
                'resume_original_name',
                'linkedin_url',
                'github_url',
                'portfolio_url',
            ]);
        });
    }
};
