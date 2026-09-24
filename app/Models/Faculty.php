<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    public const STATUS_TEACHING = 'Teaching';
    public const STATUS_ON_LEAVE = 'On leave';
    public const STATUS_INACTIVE = 'Inactive';

    protected $table = 'faculty';

    protected $primaryKey = 'faculty_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'faculty_id',
        'first_name',
        'last_name',
        'user_id',
        'dept_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dept_id', 'dept_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'faculty_id', 'faculty_id');
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
