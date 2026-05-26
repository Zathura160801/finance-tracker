<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $accounts = \App\Models\Account::with('currency')->orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        $transactionsQuery = Transaction::with(['account.currency', 'destinationAccount.currency', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('account_id')) {
            $transactionsQuery->where(function ($query) use ($request) {
                $query->where('account_id', $request->integer('account_id'))
                    ->orWhere('destination_account_id', $request->integer('account_id'));
            });
        }

        if ($request->filled('type')) {
            $transactionsQuery->where('type', $request->string('type'));
        }

        if ($request->filled('category_id')) {
            $transactionsQuery->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('date_from')) {
            $transactionsQuery->whereDate('transaction_date', '>=', \Carbon\Carbon::parse($request->string('date_from')));
        }

        if ($request->filled('date_to')) {
            $transactionsQuery->whereDate('transaction_date', '<=', \Carbon\Carbon::parse($request->string('date_to')));
        }

        $transactions = $transactionsQuery->paginate(15)->withQueryString();
        $transactionFilters = $request->only(['account_id', 'type', 'category_id', 'date_from', 'date_to']);

        return view('transactions.index', compact('transactions', 'accounts', 'categories', 'transactionFilters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:income,expense,transfer',
            'account_id' => 'required|exists:accounts,id',
            'destination_account_id' => 'required_if:type,transfer|nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'destination_amount' => 'nullable|numeric|min:0.01',
            'category_id' => 'nullable|exists:categories,id',
            'transaction_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->transactionService->createTransaction(
                accountId: $validated['account_id'],
                type: $validated['type'],
                amount: $validated['amount'],
                categoryId: $validated['category_id'] ?? null,
                date: $validated['transaction_date'] ?? null,
                description: $validated['description'] ?? null,
                destinationAccountId: $validated['destination_account_id'] ?? null,
                destinationAmount: $validated['destination_amount'] ?? null
            );

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat transaksi: '.$e->getMessage());
        }
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:income,expense,transfer',
            'account_id' => 'required|exists:accounts,id',
            'destination_account_id' => 'required_if:type,transfer|nullable|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'destination_amount' => 'nullable|numeric|min:0.01',
            'category_id' => 'nullable|exists:categories,id',
            'transaction_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->transactionService->updateTransaction($transaction, $validated);

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui transaksi: '.$e->getMessage());
        }
    }

    public function destroy(Transaction $transaction)
    {
        try {
            $this->transactionService->deleteTransaction($transaction);

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus dan saldo dikembalikan!');
        } catch (\Exception $e) {
            return redirect()->route('transactions.index')->with('error', 'Gagal menghapus transaksi: '.$e->getMessage());
        }
    }
}
