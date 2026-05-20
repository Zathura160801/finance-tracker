<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'type', // income, expense, transfer
        'account_id',
        'destination_account_id',
        'amount',
        'destination_amount',
        'category_id',
        'transaction_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'destination_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function debtRepayment(): HasOne
    {
        return $this->hasOne(DebtRepayment::class);
    }

    public function returnedDeposit(): HasOne
    {
        return $this->hasOne(Deposit::class, 'return_transaction_id');
    }
}
