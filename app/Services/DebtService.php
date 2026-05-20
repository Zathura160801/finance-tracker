<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtRepayment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DebtService
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Create a new debt or receivable record.
     */
    public function createDebt(
        int $contactId,
        string $type, // debt (hutang), receivable (piutang)
        int $accountId,
        float $amount,
        ?string $dueDate = null,
        ?string $description = null
    ): Debt {
        return DB::transaction(function () use (
            $contactId,
            $type,
            $accountId,
            $amount,
            $dueDate,
            $description
        ) {
            $account = Account::findOrFail($accountId);

            // 1. Create cash flow transaction
            // Debt (borrowing) means cash comes IN (income)
            // Receivable (lending) means cash goes OUT (expense)
            $txType = ($type === 'debt') ? 'income' : 'expense';
            $txDescription = ($type === 'debt')
                ? 'Penerimaan hutang baru: '.($description ?? '')
                : 'Pemberian pinjaman (piutang) baru: '.($description ?? '');

            $transaction = $this->transactionService->createTransaction(
                accountId: $accountId,
                type: $txType,
                amount: $amount,
                categoryId: null, // Debts/loans don't need expense/income categories
                date: Carbon::now()->toDateTimeString(),
                description: trim($txDescription)
            );

            // 2. Create debt record
            $debt = new Debt;
            $debt->contact_id = $contactId;
            $debt->type = $type;
            $debt->account_id = $accountId;
            $debt->amount = $amount;
            $debt->remaining_amount = $amount;
            $debt->transaction_id = $transaction->id;
            $debt->due_date = $dueDate ? Carbon::parse($dueDate) : null;
            $debt->status = 'pending';
            $debt->description = $description;
            $debt->save();

            return $debt;
        });
    }

    /**
     * Update debt details and realign the linked cash flow when nominal changes.
     */
    public function updateDebt(Debt $debt, array $data): Debt
    {
        return DB::transaction(function () use ($debt, $data) {
            $debt->loadMissing('transaction', 'repayments');

            $newAmount = array_key_exists('amount', $data)
                ? (float) $data['amount']
                : (float) $debt->amount;

            $repaymentsTotal = (float) $debt->repayments()->sum('amount');

            if ($newAmount < $repaymentsTotal) {
                throw new \InvalidArgumentException('Nominal baru tidak boleh lebih kecil dari total cicilan yang sudah dibayar.');
            }

            if ($debt->transaction) {
                $txType = $debt->type === 'debt' ? 'income' : 'expense';
                $this->transactionService->updateTransaction($debt->transaction, [
                    'type' => $txType,
                    'account_id' => $debt->account_id,
                    'amount' => $newAmount,
                    'description' => $this->buildDebtTransactionDescription($debt->type, $data['description'] ?? $debt->description),
                    'transaction_date' => $debt->transaction->transaction_date,
                ]);
            }

            $debt->contact_id = $data['contact_id'] ?? $debt->contact_id;
            $debt->due_date = array_key_exists('due_date', $data)
                ? ($data['due_date'] ? Carbon::parse($data['due_date']) : null)
                : $debt->due_date;
            $debt->description = array_key_exists('description', $data)
                ? $data['description']
                : $debt->description;
            $debt->amount = $newAmount;
            $debt->remaining_amount = max(0, $newAmount - $repaymentsTotal);
            $debt->status = $debt->remaining_amount <= 0.01 ? 'paid_off' : 'pending';
            $debt->save();

            return $debt;
        });
    }

    /**
     * Record a repayment for a debt or receivable.
     */
    public function recordRepayment(
        int $debtId,
        int $accountId,
        float $amount,
        ?string $description = null
    ): DebtRepayment {
        return DB::transaction(function () use ($debtId, $accountId, $amount, $description) {
            $debt = Debt::findOrFail($debtId);

            if ($debt->status === 'paid_off') {
                throw new \InvalidArgumentException('This debt has already been fully paid off.');
            }

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Repayment amount must be positive.');
            }

            // Cap repayment at remaining amount
            if ($amount > $debt->remaining_amount) {
                $amount = $debt->remaining_amount;
            }

            // 1. Create cash flow transaction
            // Debt payback (we pay) means cash goes OUT (expense)
            // Receivable payback (we receive) means cash comes IN (income)
            $txType = ($debt->type === 'debt') ? 'expense' : 'income';
            $txDescription = ($debt->type === 'debt')
                ? 'Pembayaran cicilan hutang: '.($description ?? '')
                : 'Penerimaan cicilan piutang: '.($description ?? '');

            $transaction = $this->transactionService->createTransaction(
                accountId: $accountId,
                type: $txType,
                amount: $amount,
                categoryId: null,
                date: Carbon::now()->toDateTimeString(),
                description: trim($txDescription)
            );

            // 2. Update remaining amount in debt record
            $debt->remaining_amount -= $amount;
            if ($debt->remaining_amount <= 0.01) { // Floating point safeguard
                $debt->remaining_amount = 0;
                $debt->status = 'paid_off';
            }
            $debt->save();

            // 3. Create debt repayment record
            $repayment = new DebtRepayment;
            $repayment->debt_id = $debtId;
            $repayment->transaction_id = $transaction->id;
            $repayment->amount = $amount;
            $repayment->save();

            return $repayment;
        });
    }

    /**
     * Delete a debt and all its cash flow transactions.
     */
    public function deleteDebt(Debt $debt): void
    {
        DB::transaction(function () use ($debt) {
            $debt->loadMissing('repayments.transaction', 'transaction');

            // Delete all repayments and reverse their cash flows
            foreach ($debt->repayments as $repayment) {
                if ($repayment->transaction) {
                    $this->transactionService->deleteTransaction($repayment->transaction);
                }
                $repayment->delete();
            }

            // Delete the initial transaction
            if ($debt->transaction_id) {
                $transaction = Transaction::find($debt->transaction_id);
                if ($transaction) {
                    $this->transactionService->deleteTransaction($transaction);
                }
            }

            $debt->delete();
        });
    }

    /**
     * Delete a debt repayment and reverse its cash flow transaction.
     */
    public function deleteRepayment(DebtRepayment $repayment): void
    {
        DB::transaction(function () use ($repayment) {
            $debt = $repayment->debt;

            // Reverse the remaining amount
            $debt->remaining_amount += $repayment->amount;
            $debt->status = 'pending'; // Since remaining amount increased, it's no longer fully paid off
            $debt->save();

            if ($repayment->transaction) {
                $this->transactionService->deleteTransaction($repayment->transaction);
            }

            $repayment->delete();
        });
    }

    private function buildDebtTransactionDescription(string $type, ?string $description): string
    {
        $prefix = $type === 'debt'
            ? 'Penerimaan hutang baru: '
            : 'Pemberian pinjaman (piutang) baru: ';

        return trim($prefix.($description ?? ''));
    }
}
