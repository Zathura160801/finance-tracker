<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\Debt;
use App\Models\Deposit;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $accounts = Account::with('currency')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $currencies = Currency::orderBy('code')->get();
        $currencyMap = $currencies->keyBy('code');

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $monthlyTransactions = Transaction::with(['account.currency', 'category'])
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->get();

        $monthlyIncomeIdr = $this->sumTransactionsInCurrency($monthlyTransactions->where('type', 'income'), $currencyMap, 'IDR');
        $monthlyExpenseIdr = $this->sumTransactionsInCurrency($monthlyTransactions->where('type', 'expense'), $currencyMap, 'IDR');

        $expenseCategoryStats = $this->buildCategoryStats(
            $monthlyTransactions->where('type', 'expense')->groupBy('category_id'),
            $currencyMap,
            $monthlyExpenseIdr,
            'Tanpa Kategori'
        );

        $incomeCategoryStats = $this->buildCategoryStats(
            $monthlyTransactions->where('type', 'income')->groupBy('category_id'),
            $currencyMap,
            $monthlyIncomeIdr,
            'Tanpa Kategori'
        );

        $assetSeparated = $accounts->groupBy('currency_code')->map(function (Collection $group, string $currencyCode) use ($currencyMap) {
            $currency = $currencyMap->get($currencyCode);

            return [
                'code' => $currencyCode,
                'name' => $currency?->name ?? $currencyCode,
                'symbol' => $currency?->symbol ?? '',
                'total' => (float) $group->sum('balance'),
            ];
        });

        $netWorthByCurrency = collect(['IDR', 'TWD', 'USD'])->mapWithKeys(function (string $targetCode) use ($accounts, $currencyMap) {
            $targetCurrency = $currencyMap->get($targetCode);

            return [$targetCode => $this->convertAccountsToCurrency($accounts, $targetCurrency, $currencyMap)];
        });

        $netWorthIdr = $netWorthByCurrency->get('IDR', 0.0);

        $recentTransactions = Transaction::with(['account.currency', 'destinationAccount.currency', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'accounts',
            'categories',
            'recentTransactions',
            'currencies',
            'assetSeparated',
            'netWorthByCurrency',
            'netWorthIdr'
        ) + [
            'monthlyIncomeIdr' => $monthlyIncomeIdr,
            'monthlyExpenseIdr' => $monthlyExpenseIdr,
            'expenseCategoryStats' => $expenseCategoryStats,
            'incomeCategoryStats' => $incomeCategoryStats,
        ]);
    }

    private function sumTransactionsInCurrency(Collection $transactions, Collection $currencyMap, string $targetCode): float
    {
        $targetCurrency = $currencyMap->get($targetCode);

        return $transactions->sum(function (Transaction $transaction) use ($currencyMap, $targetCurrency) {
            return $this->convertAmount(
                (float) $transaction->amount,
                $currencyMap->get($transaction->account?->currency_code),
                $targetCurrency,
            );
        });
    }

    private function buildCategoryStats(Collection $groupedTransactions, Collection $currencyMap, float $totalBaseAmount, string $fallbackName): Collection
    {
        return $groupedTransactions->map(function (Collection $transactions, $categoryId) use ($currencyMap, $totalBaseAmount, $fallbackName) {
            $firstTransaction = $transactions->first();
            $category = $firstTransaction?->category;
            $amount = $transactions->sum(function (Transaction $transaction) use ($currencyMap) {
                return $this->convertAmount(
                    (float) $transaction->amount,
                    $currencyMap->get($transaction->account?->currency_code),
                    $currencyMap->get('IDR')
                );
            });

            return [
                'category_id' => $categoryId,
                'name' => $category?->name ?? $fallbackName,
                'color' => $category?->color ?? '#64748b',
                'amount' => $amount,
                'formatted' => 'Rp '.number_format($amount, 2, ',', '.'),
                'percentage' => $totalBaseAmount > 0 ? round(($amount / $totalBaseAmount) * 100, 1) : 0,
            ];
        })->sortByDesc('amount')->values();
    }

    private function convertAccountsToCurrency(Collection $accounts, ?Currency $targetCurrency, Collection $currencyMap): float
    {
        if (! $targetCurrency) {
            return 0.0;
        }

        return $accounts->sum(function (Account $account) use ($targetCurrency, $currencyMap) {
            return $this->convertAmount(
                (float) $account->balance,
                $currencyMap->get($account->currency_code),
                $targetCurrency
            );
        });
    }

    private function convertAmount(float $amount, ?Currency $fromCurrency, ?Currency $toCurrency): float
    {
        $fromRate = (float) ($fromCurrency?->exchange_rate_to_usd ?? 1.0);
        $toRate = (float) ($toCurrency?->exchange_rate_to_usd ?? 1.0);

        if ($fromRate <= 0 || $toRate <= 0) {
            return $amount;
        }

        return ($amount / $fromRate) * $toRate;
    }
}
