<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->unsignedBigInteger('debit_account_id');
            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('currency_code', 10);
            $table->bigInteger('amount');
            $table->string('counterparty_name');
            $table->string('counterparty_code');
            $table->string('counterparty_bank_code')->nullable();
            $table->unsignedBigInteger('counterparty_id')->nullable();
            $table->unsignedBigInteger('counterparty_transaction_id')->nullable();
            $table->string('description')->nullable();
            $table->string('note')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('kind')->nullable()->comment('parent, fee, rollback');
            $table->uuid('parent_uuid')->nullable();
            $table->boolean('is_intrabank')->default(false);
            $table->timestamp('realized_at')->nullable();
            $table->timestamps();

            $table->foreign('debit_account_id')->references('id')->on('debit_accounts');
            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('person_id')->references('id')->on('people')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('debit_transaction_categories')->onUpdate('cascade');
            $table->foreign('counterparty_id')->references('id')->on('debit_accounts');
            $table->foreign('counterparty_transaction_id')->references('id')->on('debit_transactions')->onUpdate('cascade');

            $table->index('debit_account_id');
            $table->index('currency_code');
            $table->index('created_at');
            $table->index(['debit_account_id', 'amount'], 'index_debit_amount');
            $table->index(['debit_account_id', 'created_at'], 'debit_transaction_account_created_index');
            $table->index(['debit_account_id', 'created_at', 'amount'], 'idx_account_created_amount');
            $table->index('kind', 'index_kind');
            $table->index(['parent_uuid', 'kind'], 'index_parent_uuid_kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_transactions');
    }
};
