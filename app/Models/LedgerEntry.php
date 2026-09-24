<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    protected $table = 'ledger_entries';

    protected $primaryKey = 'ledger_entry_id';

    public const TYPE_CHARGE = 'CHARGE';
    public const TYPE_PAYMENT = 'PAYMENT';

    protected $fillable = [
        'student_id',
        'description',
        'entry_type',
        'amount',
        'recorded_by_user_id',
        'entry_date',
    ];

    protected $casts = [
        'amount' => 'float',
        'entry_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id', 'user_id');
    }

    /** Charges increase the balance owed; payments reduce it. */
    public function signedAmount(): float
    {
        return $this->entry_type === self::TYPE_CHARGE ? $this->amount : -$this->amount;
    }
}
