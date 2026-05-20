<?php

namespace App\Http/Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Debt;
use App\Models\Deposit;
use App\Models\Currency;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $accounts = Account::with('currency')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $incomeCategories = $categories->where('type', 'income');
        $expenseCategories = $categories->where('type', 'expense');
        
        $recentTransactions = Transaction::with(['account.currency', 'destinationAccount.currency', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $contacts = Contact::orderBy('name')->get();

        $debts = Debt::with(['contact', 'account.currency'])
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();

        $deposits = Deposit::with('account.currency')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $currencies = Currency::all();

        // Calculate Net Worth in IDR (Base currency)
        $idrCurrency = Currency::where('code', 'IDR')->first();
        $idrRate = $idrCurrency ? $idrCurrency->exchange_rate_to_usd : 16000.00;
        
        $netWorthIdr = 0.00;
        foreach ($accounts as $account) {
            $accountRate = $account->currency->exchange_rate_to_usd ?? 1.0;
            if ($accountRate > 0) {
                // Convert balance to USD then to IDR
                $usdValue = $account->balance / $accountRate;
                $idrValue = $usdValue * $idrRate;
                $netWorthIdr += $idrValue;
            }
        }

        return view('dashboard', compact(
            'accounts',
            'incomeCategories',
            'expenseCategories',
            'recentTransactions',
            'contacts',
            'debts',
            'deposits',
            'currencies',
            'netWorthIdr'
        ));
    }
}
