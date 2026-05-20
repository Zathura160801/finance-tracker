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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // income, expense, transfer
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('destination_amount', 15, 2)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->dateTime('transaction_date');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
