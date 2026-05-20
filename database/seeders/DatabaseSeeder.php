<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Account;
use App\Models\Contact;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Currencies
        $currencies = [
            [
                'code' => 'IDR',
                'name' => 'Rupiah',
                'symbol' => 'Rp',
                'exchange_rate_to_usd' => 16000.000000,
            ],
            [
                'code' => 'TWD',
                'name' => 'New Taiwan Dollar',
                'symbol' => 'NT$',
                'exchange_rate_to_usd' => 32.000000,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate_to_usd' => 1.000000,
            ],
        ];

        foreach ($currencies as $curr) {
            Currency::updateOrCreate(['code' => $curr['code']], $curr);
        }

        // 2. Seed Categories
        $categories = [
            // Expenses
            ['name' => 'Makan & Minuman', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#ef4444'],
            ['name' => 'Pulsa & Internet', 'type' => 'expense', 'icon' => 'phone', 'color' => '#3b82f6'],
            ['name' => 'Belanja Bulanan', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#10b981'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'car', 'color' => '#f59e0b'],
            ['name' => 'Utilitas (Listrik/Air)', 'type' => 'expense', 'icon' => 'bolt', 'color' => '#8b5cf6'],
            ['name' => 'Hiburan & Rekreasi', 'type' => 'expense', 'icon' => 'film', 'color' => '#ec4899'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'heart-pulse', 'color' => '#14b8a6'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'graduation-cap', 'color' => '#6366f1'],
            ['name' => 'Lain-lain (Pengeluaran)', 'type' => 'expense', 'icon' => 'help-circle', 'color' => '#6b7280'],

            // Incomes
            ['name' => 'Gaji & Upah', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#22c55e'],
            ['name' => 'Investasi', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#a855f7'],
            ['name' => 'Transfer Masuk', 'type' => 'income', 'icon' => 'arrow-down-left', 'color' => '#06b6d4'],
            ['name' => 'Hadiah / Hibah', 'type' => 'income', 'icon' => 'gift', 'color' => '#f43f5e'],
            ['name' => 'Sampingan (Freelance)', 'type' => 'income', 'icon' => 'laptop', 'color' => '#eab308'],
            ['name' => 'Lain-lain (Pemasukan)', 'type' => 'income', 'icon' => 'plus-circle', 'color' => '#4b5563'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name'], 'type' => $cat['type']],
                $cat
            );
        }

        // 3. Seed Default Accounts
        $accounts = [
            [
                'name' => 'Uang Cash (IDR)',
                'account_number' => null,
                'type' => 'cash',
                'currency_code' => 'IDR',
                'balance' => 500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bank BCA (Indo)',
                'account_number' => '1234567890',
                'type' => 'bank',
                'currency_code' => 'IDR',
                'balance' => 10000000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bank Taiwan (BOT)',
                'account_number' => '9876543210',
                'type' => 'bank',
                'currency_code' => 'TWD',
                'balance' => 20000.00,
                'is_active' => true,
            ],
            [
                'name' => 'EasyCard Taiwan',
                'account_number' => '55443322',
                'type' => 'card',
                'currency_code' => 'TWD',
                'balance' => 500.00,
                'is_active' => true,
            ],
            [
                'name' => 'Wallet GoPay',
                'account_number' => '08123456789',
                'type' => 'e_wallet',
                'currency_code' => 'IDR',
                'balance' => 250000.00,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $acc) {
            Account::updateOrCreate(['name' => $acc['name']], $acc);
        }

        // 4. Seed Default Contacts for Debts
        $contacts = [
            ['name' => 'Budi', 'phone' => '0811223344', 'description' => 'Teman kantor'],
            ['name' => 'Auntie Taiwan', 'phone' => '+8869123456', 'description' => 'Ibu kos Taiwan'],
        ];

        foreach ($contacts as $con) {
            Contact::updateOrCreate(['name' => $con['name']], $con);
        }
    }
}
