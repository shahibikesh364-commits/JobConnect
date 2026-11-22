<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Conversation extends Model
{
    protected $fillable = [
        'job_seeker_id',
        'employer_id',
        'job_listing_id',
    ];

    protected function casts(): array
    {
        return [
            'job_seeker_archived_at' => 'datetime',
            'employer_archived_at' => 'datetime',
        ];
    }

    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobListing::class, 'job_listing_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function archivedAtFor(User $user)
    {
        return $user->isJobSeeker()
            ? $this->job_seeker_archived_at
            : $this->employer_archived_at;
    }

    public function setArchivedFor(User $user, bool $archived): void
    {
        $column = $user->isJobSeeker()
            ? 'job_seeker_archived_at'
            : 'employer_archived_at';

        $this->{$column} = $archived ? now() : null;
        $this->save();
    }
}
