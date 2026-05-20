<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->index(['type', 'transaction_date'], 'transactions_type_date_index');
            $table->index(['account_id', 'transaction_date'], 'transactions_account_date_index');
            $table->index(['destination_account_id', 'transaction_date'], 'transactions_destination_account_date_index');
            $table->index('category_id', 'transactions_category_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex('transactions_type_date_index');
            $table->dropIndex('transactions_account_date_index');
            $table->dropIndex('transactions_destination_account_date_index');
            $table->dropIndex('transactions_category_index');
        });
    }
};
