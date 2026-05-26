<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    protected DebtService $debtService;

    public function __construct(DebtService $debtService)
    {
        $this->debtService = $debtService;
    }

    public function index()
    {
        $accounts = \App\Models\Account::with('currency')->orderBy('name')->get();
        $contacts = Contact::orderBy('name')->get();

        $debts = Debt::with(['contact', 'account.currency', 'repayments.transaction'])
            ->orderBy('status', 'asc') // pending first
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('debts.index', compact('debts', 'contacts', 'accounts'));
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:500',
        ]);

        Contact::create($validated);

        return redirect()->route('debts.index')->with('success', 'Kontak baru berhasil ditambahkan!');
    }

    public function storeDebt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'type' => 'required|string|in:debt,receivable',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->debtService->createDebt(
                contactId: $validated['contact_id'],
                type: $validated['type'],
                accountId: $validated['account_id'],
                amount: $validated['amount'],
                dueDate: $validated['due_date'] ?? null,
                description: $validated['description'] ?? null
            );

            $label = ($validated['type'] === 'debt') ? 'Hutang' : 'Piutang';

            return redirect()->route('debts.index')->with('success', "$label baru berhasil dicatat!");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat hutang/piutang: '.$e->getMessage());
        }
    }

    public function storeRepayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'debt_id' => 'required|exists:debts,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->debtService->recordRepayment(
                debtId: $validated['debt_id'],
                accountId: $validated['account_id'],
                amount: $validated['amount'],
                description: $validated['description'] ?? null
            );

            return redirect()->route('debts.index')->with('success', 'Pembayaran cicilan berhasil dicatat!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat cicilan: '.$e->getMessage());
        }
    }

    public function update(Request $request, Debt $debt): RedirectResponse
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $this->debtService->updateDebt($debt, $validated);

            return redirect()->route('debts.index')->with('success', 'Catatan hutang/piutang berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui hutang/piutang: '.$e->getMessage());
        }
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        try {
            $this->debtService->deleteDebt($debt);

            return redirect()->route('debts.index')->with('success', 'Catatan hutang/piutang berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('debts.index')->with('error', 'Gagal menghapus hutang/piutang: '.$e->getMessage());
        }
    }
}
