<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $primaryKey = 'announcement_id';

    public const AUDIENCE_ALL = 'All';
    public const AUDIENCE_STUDENTS = 'Students';
    public const AUDIENCE_FACULTY = 'Faculty';

    protected $fillable = [
        'title',
        'content',
        'date_posted',
        'target_audience',
        'author_id',
    ];

    protected $casts = [
        'date_posted' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function scopeVisibleTo($query, string $role)
    {
        $audience = $role === User::ROLE_STUDENT ? self::AUDIENCE_STUDENTS : self::AUDIENCE_FACULTY;

        return $query->whereIn('target_audience', [self::AUDIENCE_ALL, $audience]);
    }
}
