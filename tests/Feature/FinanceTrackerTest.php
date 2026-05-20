<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Debt;
use App\Models\Deposit;
use App\Services\TransactionService;
use App\Services\DebtService;
use App\Services\DepositService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected Currency $idr;
    protected Currency $twd;
    protected Category $makan;
    protected Category $gaji;
    protected Account $cashIdr;
    protected Account $bankTwd;
    protected Contact $budi;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed currencies
        $this->idr = Currency::create([
            'code' => 'IDR',
            'name' => 'Rupiah',
            'symbol' => 'Rp',
            'exchange_rate_to_usd' => 16000.00
        ]);

        $this->twd = Currency::create([
            'code' => 'TWD',
            'name' => 'Taiwan Dollar',
            'symbol' => 'NT$',
            'exchange_rate_to_usd' => 32.00
        ]);

        // 2. Seed categories
        $this->makan = Category::create([
            'name' => 'Makan',
            'type' => 'expense'
        ]);

        $this->gaji = Category::create([
            'name' => 'Gaji',
            'type' => 'income'
        ]);

        // 3. Seed accounts
        $this->cashIdr = Account::create([
            'name' => 'Cash IDR',
            'type' => 'cash',
            'currency_code' => 'IDR',
            'balance' => 100000.00
        ]);

        $this->bankTwd = Account::create([
            'name' => 'Bank TWD',
            'type' => 'bank',
            'currency_code' => 'TWD',
            'balance' => 1000.00
        ]);

        // 4. Seed contact
        $this->budi = Contact::create([
            'name' => 'Budi'
        ]);
    }

    /**
     * Test basic income and expense transactions.
     */
    public function test_income_and_expense_transactions()
    {
        $transactionService = app(TransactionService::class);

        // Expense transaction
        $transactionService->createTransaction(
            accountId: $this->cashIdr->id,
            type: 'expense',
            amount: 20000.00,
            categoryId: $this->makan->id,
            description: 'Makan siang bakso'
        );

        $this->cashIdr->refresh();
        $this->assertEquals(80000.00, $this->cashIdr->balance);

        // Income transaction
        $transactionService->createTransaction(
            accountId: $this->cashIdr->id,
            type: 'income',
            amount: 50000.00,
            categoryId: $this->gaji->id,
            description: 'Uang jajan'
        );

        $this->cashIdr->refresh();
        $this->assertEquals(130000.00, $this->cashIdr->balance);
    }

    /**
     * Test transfer between accounts (same currency).
     */
    public function test_transfer_same_currency()
    {
        $transactionService = app(TransactionService::class);

        $cashIdr2 = Account::create([
            'name' => 'Cash IDR Kedua',
            'type' => 'cash',
            'currency_code' => 'IDR',
            'balance' => 10000.00
        ]);

        $transactionService->createTransaction(
            accountId: $this->cashIdr->id,
            type: 'transfer',
            amount: 30000.00,
            destinationAccountId: $cashIdr2->id,
            description: 'Transfer internal'
        );

        $this->cashIdr->refresh();
        $cashIdr2->refresh();

        $this->assertEquals(70000.00, $this->cashIdr->balance);
        $this->assertEquals(40000.00, $cashIdr2->balance);
    }

    /**
     * Test cross-currency transfers.
     */
    public function test_cross_currency_transfer()
    {
        $transactionService = app(TransactionService::class);

        // Transfer 50,000 IDR to Bank TWD (receiving 100 TWD)
        $transactionService->createTransaction(
            accountId: $this->cashIdr->id,
            type: 'transfer',
            amount: 50000.00,
            destinationAccountId: $this->bankTwd->id,
            destinationAmount: 100.00,
            description: 'Beli TWD'
        );

        $this->cashIdr->refresh();
        $this->bankTwd->refresh();

        $this->assertEquals(50000.00, $this->cashIdr->balance);
        $this->assertEquals(1100.00, $this->bankTwd->balance);
    }

    /**
     * Test debt creation and repayment logic.
     */
    public function test_debt_and_repayment()
    {
        $debtService = app(DebtService::class);

        // 1. Create a debt (we borrow 50,000 IDR -> enters cash account)
        $debt = $debtService->createDebt(
            contactId: $this->budi->id,
            type: 'debt',
            accountId: $this->cashIdr->id,
            amount: 50000.00,
            description: 'Hutang makan'
        );

        $this->cashIdr->refresh();
        $this->assertEquals(150000.00, $this->cashIdr->balance);
        $this->assertEquals(50000.00, $debt->remaining_amount);
        $this->assertEquals('pending', $debt->status);

        // 2. Repay 30,000 IDR (we pay Budi -> leaves cash account)
        $repayment = $debtService->recordRepayment(
            debtId: $debt->id,
            accountId: $this->cashIdr->id,
            amount: 30000.00,
            description: 'Cicil bayar budi'
        );

        $this->cashIdr->refresh();
        $debt->refresh();

        $this->assertEquals(120000.00, $this->cashIdr->balance);
        $this->assertEquals(20000.00, $debt->remaining_amount);
        $this->assertEquals('pending', $debt->status);

        // 3. Fully repay the rest 20,000 IDR
        $debtService->recordRepayment(
            debtId: $debt->id,
            accountId: $this->cashIdr->id,
            amount: 20000.00,
            description: 'Pelunasan budi'
        );

        $this->cashIdr->refresh();
        $debt->refresh();

        $this->assertEquals(100000.00, $this->cashIdr->balance);
        $this->assertEquals(0.00, $debt->remaining_amount);
        $this->assertEquals('paid_off', $debt->status);
    }

    /**
     * Test deposit creation and return logic.
     */
    public function test_deposit_and_return()
    {
        $depositService = app(DepositService::class);

        // 1. Pay deposit of 300 TWD from Bank TWD (leaves Bank TWD)
        $deposit = $depositService->createDeposit(
            name: 'Deposit Rental Sepeda',
            accountId: $this->bankTwd->id,
            amount: 300.00,
            description: 'Uang jaminan YouBike'
        );

        $this->bankTwd->refresh();
        $this->assertEquals(700.00, $this->bankTwd->balance);
        $this->assertEquals('active', $deposit->status);

        // 2. Return deposit of 300 TWD into Bank TWD (enters Bank TWD)
        $depositService->returnDeposit(
            depositId: $deposit->id,
            accountId: $this->bankTwd->id,
            description: 'Sepeda dikembalikan aman'
        );

        $this->bankTwd->refresh();
        $deposit->refresh();

        $this->assertEquals(1000.00, $this->bankTwd->balance);
        $this->assertEquals('returned', $deposit->status);
        $this->assertNotNull($deposit->return_transaction_id);
    }
}
