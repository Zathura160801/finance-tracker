<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\DebtRepayment;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                ? "Penerimaan hutang baru: " . ($description ?? '')
                : "Pemberian pinjaman (piutang) baru: " . ($description ?? '');

            $transaction = $this->transactionService->createTransaction(
                accountId: $accountId,
                type: $txType,
                amount: $amount,
                categoryId: null, // Debts/loans don't need expense/income categories
                date: Carbon::now()->toDateTimeString(),
                description: trim($txDescription)
            );

            // 2. Create debt record
            $debt = new Debt();
            $debt->contact_id = $contactId;
            $debt->type = $type;
            $debt->account_id = $accountId;
            $debt->amount = $amount;
            $debt->remaining_amount = $amount;
            $debt->due_date = $dueDate ? Carbon::parse($dueDate) : null;
            $debt->status = 'pending';
            $debt->description = $description;
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
                ? "Pembayaran cicilan hutang: " . ($description ?? '')
                : "Penerimaan cicilan piutang: " . ($description ?? '');

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
            $repayment = new DebtRepayment();
            $repayment->debt_id = $debtId;
            $repayment->transaction_id = $transaction->id;
            $repayment->amount = $amount;
            $repayment->save();

            return $repayment;
        });
    }
}
