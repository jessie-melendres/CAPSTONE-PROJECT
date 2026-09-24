<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'departments';

    protected $primaryKey = 'dept_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'dept_id',
        'dept_name',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'dept_id', 'dept_id');
    }

    public function faculty(): HasMany
    {
        return $this->hasMany(Faculty::class, 'dept_id', 'dept_id');
    }
}
