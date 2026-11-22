<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerTeamMember extends Model
{
    protected $fillable = [
        'employer_id', 'member_user_id', 'invited_by', 'name', 'email', 'role', 'status',
        'invitation_token', 'invited_at', 'accepted_at',
    ];

    protected $hidden = ['invitation_token'];

    protected function casts(): array
    {
        return ['invited_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }
}
