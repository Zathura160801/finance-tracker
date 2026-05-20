<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepositService
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Create a new deposit record and the corresponding expense cash flow.
     */
    public function createDeposit(
        string $name,
        int $accountId,
        float $amount,
        ?string $description = null
    ): Deposit {
        return DB::transaction(function () use ($name, $accountId, $amount, $description) {
            $account = Account::findOrFail($accountId);

            // 1. Create cash flow transaction (deposit is an expense cash flow)
            $txDescription = "Pembayaran deposit jaminan: " . $name . ($description ? " (" . $description . ")" : "");
            
            $transaction = $this->transactionService->createTransaction(
                accountId: $accountId,
                type: 'expense',
                amount: $amount,
                categoryId: null,
                date: Carbon::now()->toDateTimeString(),
                description: $txDescription
            );

            // 2. Create deposit record
            $deposit = new Deposit();
            $deposit->name = $name;
            $deposit->account_id = $accountId;
            $deposit->amount = $amount;
            $deposit->status = 'active';
            $deposit->description = $description;
            $deposit->save();

            return $deposit;
        });
    }

    /**
     * Mark a deposit as returned and create the corresponding income cash flow.
     */
    public function returnDeposit(int $depositId, int $accountId, ?string $description = null): Deposit
    {
        return DB::transaction(function () use ($depositId, $accountId, $description) {
            $deposit = Deposit::findOrFail($depositId);

            if ($deposit->status === 'returned') {
                throw new \InvalidArgumentException('This deposit has already been returned.');
            }

            $account = Account::findOrFail($accountId);

            // 1. Create cash flow transaction (return of deposit is an income cash flow)
            $txDescription = "Pengembalian deposit jaminan: " . $deposit->name . ($description ? " (" . $description . ")" : "");
            
            $transaction = $this->transactionService->createTransaction(
                accountId: $accountId,
                type: 'income',
                amount: $deposit->amount,
                categoryId: null,
                date: Carbon::now()->toDateTimeString(),
                description: $txDescription
            );

            // 2. Update deposit status and reference
            $deposit->status = 'returned';
            $deposit->return_transaction_id = $transaction->id;
            if ($description) {
                $deposit->description = trim(($deposit->description ?? '') . " | Catatan Pengembalian: " . $description);
            }
            $deposit->save();

            return $deposit;
        });
    }
}
