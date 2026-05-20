<?php

namespace App\Http\Controllers;

use App\Services\DepositService;
use App\Models\Deposit;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    protected DepositService $depositService;

    public function __construct(DepositService $depositService)
    {
        $this->depositService = $depositService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->depositService->createDeposit(
                name: $validated['name'],
                accountId: $validated['account_id'],
                amount: $validated['amount'],
                description: $validated['description'] ?? null
            );

            return redirect()->route('dashboard')->with('success', 'Deposit jaminan baru berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat deposit: ' . $e->getMessage());
        }
    }

    public function return(Request $request)
    {
        $validated = $request->validate([
            'deposit_id' => 'required|exists:deposits,id',
            'account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->depositService->returnDeposit(
                depositId: $validated['deposit_id'],
                accountId: $validated['account_id'],
                description: $validated['description'] ?? null
            );

            return redirect()->route('dashboard')->with('success', 'Pengembalian deposit berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }
}
