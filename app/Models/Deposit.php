<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    protected $fillable = [
        'name',
        'account_id',
        'amount',
        'status', // active, returned
        'return_transaction_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function returnTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'return_transaction_id');
    }
}
