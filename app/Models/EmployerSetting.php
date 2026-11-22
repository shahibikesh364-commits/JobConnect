<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerSetting extends Model
{
    protected $fillable = [
        'employer_id', 'company_name', 'company_logo', 'tagline', 'industry', 'company_size',
        'founded_year', 'website', 'about_company', 'country', 'city', 'full_address', 'social_links',
        'contact_person', 'contact_phone', 'language', 'timezone', 'notification_preferences',
        'default_job_type', 'application_deadline_days', 'auto_close_jobs', 'application_alert_threshold',
        'screening_questions', 'public_profile',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'notification_preferences' => 'array',
            'screening_questions' => 'array',
            'auto_close_jobs' => 'boolean',
            'public_profile' => 'boolean',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }
}
