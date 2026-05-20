<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Create a new transaction (income, expense, or transfer).
     */
    public function createTransaction(
        int $accountId,
        string $type, // income, expense, transfer
        float $amount,
        ?int $categoryId = null,
        ?string $date = null,
        ?string $description = null,
        ?int $destinationAccountId = null,
        ?float $destinationAmount = null
    ): Transaction {
        return DB::transaction(function () use (
            $accountId,
            $type,
            $amount,
            $categoryId,
            $date,
            $description,
            $destinationAccountId,
            $destinationAmount
        ) {
            $account = Account::findOrFail($accountId);
            $transactionDate = $date ? Carbon::parse($date) : Carbon::now();

            $transaction = new Transaction;
            $transaction->type = $type;
            $transaction->account_id = $accountId;
            $transaction->amount = $amount;
            $transaction->category_id = $categoryId;
            $transaction->transaction_date = $transactionDate;
            $transaction->description = $description;

            if ($type === 'income') {
                // Adjust account balance (increase)
                $this->accountService->adjustBalance($account, $amount);
            } elseif ($type === 'expense') {
                // Adjust account balance (decrease)
                $this->accountService->adjustBalance($account, -$amount);
            } elseif ($type === 'transfer') {
                if (! $destinationAccountId) {
                    throw new \InvalidArgumentException('Destination account is required for transfers.');
                }

                $destinationAccount = Account::findOrFail($destinationAccountId);
                $destAmount = $destinationAmount ?? $amount;

                $transaction->destination_account_id = $destinationAccountId;
                $transaction->destination_amount = $destAmount;

                // Adjust balances
                $this->accountService->adjustBalance($account, -$amount);
                $this->accountService->adjustBalance($destinationAccount, $destAmount);
            }

            $transaction->save();

            return $transaction;
        });
    }

    /**
     * Update an existing transaction and keep balances consistent.
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->loadMissing('account', 'destinationAccount');

            $this->reverseTransactionEffects($transaction);

            $transaction->type = $data['type'] ?? $transaction->type;
            $transaction->account_id = $data['account_id'] ?? $transaction->account_id;
            $transaction->amount = $data['amount'] ?? $transaction->amount;
            $transaction->category_id = array_key_exists('category_id', $data) ? $data['category_id'] : $transaction->category_id;
            $transaction->transaction_date = isset($data['transaction_date'])
                ? Carbon::parse($data['transaction_date'])
                : $transaction->transaction_date;
            $transaction->description = array_key_exists('description', $data)
                ? $data['description']
                : $transaction->description;
            $transaction->destination_account_id = $transaction->type === 'transfer'
                ? ($data['destination_account_id'] ?? $transaction->destination_account_id)
                : null;
            $transaction->destination_amount = $transaction->type === 'transfer'
                ? ($data['destination_amount'] ?? $data['amount'] ?? $transaction->destination_amount)
                : null;

            $transaction->unsetRelation('account');
            $transaction->unsetRelation('destinationAccount');
            $transaction->load('account', 'destinationAccount');
            $this->applyTransactionEffects($transaction);

            $transaction->save();

            return $transaction;
        });
    }

    /**
     * Delete a transaction and reverse its balance effects.
     */
    public function deleteTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->loadMissing('account', 'destinationAccount');

            $this->reverseTransactionEffects($transaction);

            $transaction->delete();
        });
    }

    private function reverseTransactionEffects(Transaction $transaction): void
    {
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $this->accountService->adjustBalance($account, -$transaction->amount);
        } elseif ($transaction->type === 'expense') {
            $this->accountService->adjustBalance($account, $transaction->amount);
        } elseif ($transaction->type === 'transfer') {
            $destinationAccount = $transaction->destinationAccount;
            $this->accountService->adjustBalance($account, $transaction->amount);

            if ($destinationAccount) {
                $this->accountService->adjustBalance($destinationAccount, -($transaction->destination_amount ?? $transaction->amount));
            }
        }
    }

    private function applyTransactionEffects(Transaction $transaction): void
    {
        $account = $transaction->account;

        if ($transaction->type === 'income') {
            $this->accountService->adjustBalance($account, $transaction->amount);
        } elseif ($transaction->type === 'expense') {
            $this->accountService->adjustBalance($account, -$transaction->amount);
        } elseif ($transaction->type === 'transfer') {
            $destinationAccount = $transaction->destinationAccount;

            if (! $destinationAccount) {
                throw new \InvalidArgumentException('Destination account is required for transfers.');
            }

            $this->accountService->adjustBalance($account, -$transaction->amount);
            $this->accountService->adjustBalance($destinationAccount, $transaction->destination_amount ?? $transaction->amount);
        }
    }
}
