<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'type' => 'required|string|in:cash,bank,card,e_wallet,deposit',
            'currency_code' => 'required|string|exists:currencies,code',
            'balance' => 'required|numeric|min:0',
        ]);

        Account::create($validated);

        return redirect()->route('dashboard')->with('success', 'Akun berhasil ditambahkan!');
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'type' => 'required|string|in:cash,bank,card,e_wallet,deposit',
            'currency_code' => 'required|string|exists:currencies,code',
            'balance' => 'required|numeric|min:0',
        ]);

        $account->update($validated);

        return redirect()->route('dashboard')->with('success', 'Akun berhasil diperbarui!');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->transactions()->exists() || $account->incomingTransfers()->exists() || $account->debts()->exists() || $account->deposits()->exists()) {
            return redirect()->route('dashboard')->with('error', 'Akun tidak bisa dihapus karena masih memiliki transaksi atau relasi terkait.');
        }

        $account->delete();

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dihapus!');
    }
}
