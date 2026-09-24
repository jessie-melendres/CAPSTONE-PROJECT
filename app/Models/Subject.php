<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $table = 'subjects';

    protected $primaryKey = 'subject_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'subject_id',
        'subject_name',
        'units',
        'prerequisite_subject_id',
    ];

    protected $casts = [
        'units' => 'integer',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'subject_id', 'subject_id');
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'prerequisite_subject_id', 'subject_id');
    }

    /** Other subjects that name this one as their prerequisite. */
    public function dependents(): HasMany
    {
        return $this->hasMany(Subject::class, 'prerequisite_subject_id', 'subject_id');
    }
}
