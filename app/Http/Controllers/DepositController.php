<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Services\DepositService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    protected DepositService $depositService;

    public function __construct(DepositService $depositService)
    {
        $this->depositService = $depositService;
    }

    public function store(Request $request): RedirectResponse
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
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat deposit: '.$e->getMessage());
        }
    }

    public function update(Request $request, Deposit $deposit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->depositService->updateDeposit($deposit, $validated);

            return redirect()->route('dashboard')->with('success', 'Deposit berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui deposit: '.$e->getMessage());
        }
    }

    public function returnDeposit(Request $request): RedirectResponse
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
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pengembalian: '.$e->getMessage());
        }
    }

    public function destroy(Deposit $deposit): RedirectResponse
    {
        try {
            $this->depositService->deleteDeposit($deposit);

            return redirect()->route('dashboard')->with('success', 'Deposit berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Gagal menghapus deposit: '.$e->getMessage());
        }
    }
}
