<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerProfile extends Model
{
    protected $fillable = [
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
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
