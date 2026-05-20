<?php

namespace App\Services;

use App\Models\Account;

class AccountService
{
    /**
     * Adjust the balance of an account.
     * Positive amount adds to the balance, negative amount subtracts from it.
     */
    public function adjustBalance(Account $account, float $amount): void
    {
        $account->balance += $amount;
        $account->save();
    }
}
