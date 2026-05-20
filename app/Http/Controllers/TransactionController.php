<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
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

            return redirect()->route('dashboard')->with('success', 'Transaksi berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat transaksi: ' . $e->getMessage());
        }
    }

    public function destroy(Transaction $transaction)
    {
        try {
            $this->transactionService->deleteTransaction($transaction);
            return redirect()->route('dashboard')->with('success', 'Transaksi berhasil dihapus dan saldo dikembalikan!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
