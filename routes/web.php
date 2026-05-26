<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Accounts
Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

// Transactions
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

// Debts
Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
Route::post('/contacts', [DebtController::class, 'storeContact'])->name('contacts.store');
Route::post('/debts', [DebtController::class, 'storeDebt'])->name('debts.store');
Route::post('/debts/repayment', [DebtController::class, 'storeRepayment'])->name('debts.repayment');
Route::put('/debts/{debt}', [DebtController::class, 'update'])->name('debts.update');
Route::delete('/debts/{debt}', [DebtController::class, 'destroy'])->name('debts.destroy');

// Deposits
Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
Route::post('/deposits/return', [DepositController::class, 'returnDeposit'])->name('deposits.return');
Route::put('/deposits/{deposit}', [DepositController::class, 'update'])->name('deposits.update');
Route::delete('/deposits/{deposit}', [DepositController::class, 'destroy'])->name('deposits.destroy');

// Currencies - Exchange Rate Sync
Route::post('/currencies/update-rates', [CurrencyController::class, 'updateRates'])->name('currencies.update-rates');
