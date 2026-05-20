<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Deposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
            $txDescription = 'Pembayaran deposit jaminan: '.$name.($description ? ' ('.$description.')' : '');

            $transaction = $this->transactionService->createTransaction(
                accountId: $accountId,
                type: 'expense',
                amount: $amount,
                categoryId: null,
                date: Carbon::now()->toDateTimeString(),
                description: $txDescription
            );

            // 2. Create deposit record
            $deposit = new Deposit;
            $deposit->name = $name;
            $deposit->account_id = $accountId;
            $deposit->amount = $amount;
            $deposit->status = 'active';
            $deposit->transaction_id = $transaction->id;
            $deposit->description = $description;
            $deposit->save();

            return $deposit;
        });
    }

    /**
     * Update deposit details and keep its cash flow aligned.
     */
    public function updateDeposit(Deposit $deposit, array $data): Deposit
    {
        return DB::transaction(function () use ($deposit, $data) {
            $deposit->loadMissing('transaction', 'returnTransaction');

            $newAmount = array_key_exists('amount', $data)
                ? (float) $data['amount']
                : (float) $deposit->amount;

            if ($deposit->status === 'returned' && $newAmount !== (float) $deposit->amount) {
                throw new \InvalidArgumentException('Nominal deposit yang sudah dikembalikan tidak dapat diubah dari layar ini.');
            }

            if ($deposit->transaction && $deposit->status === 'active') {
                $this->transactionService->updateTransaction($deposit->transaction, [
                    'type' => 'expense',
                    'account_id' => $deposit->account_id,
                    'amount' => $newAmount,
                    'description' => $this->buildDepositDescription(
                        $data['name'] ?? $deposit->name,
                        $data['description'] ?? $deposit->description
                    ),
                    'transaction_date' => $deposit->transaction->transaction_date,
                ]);
            }

            $deposit->name = $data['name'] ?? $deposit->name;
            $deposit->description = array_key_exists('description', $data)
                ? $data['description']
                : $deposit->description;
            $deposit->amount = $newAmount;
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
            $txDescription = 'Pengembalian deposit jaminan: '.$deposit->name.($description ? ' ('.$description.')' : '');

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
                $deposit->description = trim(($deposit->description ?? '').' | Catatan Pengembalian: '.$description);
            }
            $deposit->save();

            return $deposit;
        });
    }

    /**
     * Delete a deposit and reverse its related cash flow transactions.
     */
    public function deleteDeposit(Deposit $deposit): void
    {
        DB::transaction(function () use ($deposit) {
            $deposit->loadMissing('transaction', 'returnTransaction');

            if ($deposit->returnTransaction) {
                $this->transactionService->deleteTransaction($deposit->returnTransaction);
            }

            if ($deposit->transaction) {
                $this->transactionService->deleteTransaction($deposit->transaction);
            }

            $deposit->delete();
        });
    }

    private function buildDepositDescription(string $name, ?string $description): string
    {
        return 'Pembayaran deposit jaminan: '.$name.($description ? ' ('.$description.')' : '');
    }
}
