<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function store(Request $request)
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
}
