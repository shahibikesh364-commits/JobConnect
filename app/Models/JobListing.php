<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobListing extends Model
{
    protected $fillable = [
        'employer_id',
        'title',
        'company_name',
        'location',
        'job_type',
        'description',
        'requirements',
        'salary_min',
        'salary_max',
        'application_deadline',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'application_deadline' => 'date',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
