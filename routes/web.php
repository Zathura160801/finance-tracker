<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Accounts
Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

// Transactions
Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

// Debts
Route::post('/contacts', [DebtController::class, 'storeContact'])->name('contacts.store');
Route::post('/debts', [DebtController::class, 'storeDebt'])->name('debts.store');
Route::post('/debts/repayment', [DebtController::class, 'storeRepayment'])->name('debts.repayment');
Route::put('/debts/{debt}', [DebtController::class, 'update'])->name('debts.update');
Route::delete('/debts/{debt}', [DebtController::class, 'destroy'])->name('debts.destroy');

// Deposits
Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
Route::post('/deposits/return', [DepositController::class, 'returnDeposit'])->name('deposits.return');
Route::put('/deposits/{deposit}', [DepositController::class, 'update'])->name('deposits.update');
Route::delete('/deposits/{deposit}', [DepositController::class, 'destroy'])->name('deposits.destroy');
