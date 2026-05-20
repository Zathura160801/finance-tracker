<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DepositController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Accounts
Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');

// Transactions
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

// Debts
Route::post('/contacts', [DebtController::class, 'storeContact'])->name('contacts.store');
Route::post('/debts', [DebtController::class, 'storeDebt'])->name('debts.store');
Route::post('/debts/repayment', [DebtController::class, 'storeRepayment'])->name('debts.repayment');

// Deposits
Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
Route::post('/deposits/return', [DepositController::class, 'return'])->name('deposits.return');
