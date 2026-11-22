<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $fillable = [
        'user_id',
        'degree',
        'institution',
        'field_of_study',
        'graduation_year',
        'description',

    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
