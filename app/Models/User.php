<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the jobs posted by this user.
     */
    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class, 'employer_id')
            ->where('employer_id', $this->employerWorkspaceId());
    }

    public function employerWorkspaceId(): int
    {
        return (int) ($this->employer_workspace_id ?: $this->id);
    }

    public function employerWorkspaceRole(): string
    {
        if (!$this->employer_workspace_id) return 'admin';

        return EmployerTeamMember::query()
            ->where('employer_id', $this->employer_workspace_id)
            ->where('member_user_id', $this->id)
            ->where('status', 'active')
            ->value('role') ?? 'viewer';
    }

    /**
     * Get the applications submitted by this user.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Get the jobs saved by this user.
     */
    public function savedJobs(): HasMany
    {
        return $this->hasMany(SavedJob::class);
    }

    /**
     * Get the job seeker's profile.
     */
    public function jobSeekerProfile(): HasOne
    {
        return $this->hasOne(JobSeekerProfile::class);
    }

    /**
     * Get the user's education records.
     */

    public function education(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    /**
     * Get the user's work experience records.
     */
    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class);
    }

    /**
     * Get the skills associated with the user.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->withTimestamps();
    }

    /**
     * Conversations started by this job seeker.
     */
    public function conversationsAsJobSeeker(): HasMany
    {
        return $this->hasMany(Conversation::class, 'job_seeker_id');
    }

    /**
     * Conversations belonging to this employer.
     */
    public function conversationsAsEmployer(): HasMany
    {
        return $this->hasMany(Conversation::class, 'employer_id');
    }

    /**
     * Messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Calculate profile completion consistently across job seeker pages.
     */
    public function profileCompletion(): int
    {
        $profile = $this->jobSeekerProfile;
        $profileFields = [
            $profile?->phone,
            $profile?->address,
            $profile?->date_of_birth,
            $profile?->headline,
            $profile?->about_me,
            $profile?->career_objective,
            $profile?->experience_level,
            $profile?->employment_status,
            $profile?->preferred_job_type,
            $profile?->profile_photo,
            $profile?->resume_path,
        ];

        $completedFields = collect([$this->name, $this->email, ...$profileFields])
            ->filter(fn($field) => filled($field))
            ->count();
        $completedFields += $this->relationLoaded('education')
            ? (int) $this->education->isNotEmpty()
            : (int) $this->education()->exists();
        $completedFields += $this->relationLoaded('workExperiences')
            ? (int) $this->workExperiences->isNotEmpty()
            : (int) $this->workExperiences()->exists();
        $completedFields += $this->relationLoaded('skills')
            ? (int) $this->skills->isNotEmpty()
            : (int) $this->skills()->exists();

        return (int) round(($completedFields / 16) * 100);
    }

    /**
     * Check whether the user is an employer.
     */
    public function isEmployer(): bool
    {
        return $this->role === 'employer';
    }

    /**
     * Check whether the user is a job seeker.
     */
    public function isJobSeeker(): bool
    {
        return $this->role === 'job_seeker';
    }

    /**
     * Check whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
