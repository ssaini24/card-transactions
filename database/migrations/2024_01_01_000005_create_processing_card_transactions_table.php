<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code')->unique();
            $table->string('cto_card_transaction_uuid')->nullable();
            $table->unsignedBigInteger('debit_account_id');
            $table->unsignedBigInteger('debit_transaction_id')->nullable();
            $table->unsignedBigInteger('debit_card_transaction_id')->nullable();
            $table->unsignedBigInteger('provider_transaction_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('ledger_block_uuid')->nullable();
            $table->integer('release_timeout_days')->default(0);
            $table->integer('release_timeout_hours')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('currency_code', 10);
            $table->bigInteger('amount');
            $table->bigInteger('amount_refunded')->default(0);
            $table->bigInteger('remaining_repayment_amount')->nullable();
            $table->bigInteger('fx_amount_paid')->default(0);
            $table->string('counterparty_name');
            $table->string('counterparty_code');
            $table->string('counterparty_bank_code')->nullable();
            $table->string('description')->nullable();
            $table->string('note')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('exchange_rate', 12, 6)->nullable();
            $table->string('original_reference_number')->nullable();
            $table->string('acquirer_reference_number')->nullable();
            $table->string('state_code');
            $table->boolean('is_receivable')->default(false);
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('realized_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('debit_account_id')->references('id')->on('debit_accounts');
            $table->foreign('debit_transaction_id')->references('id')->on('debit_transactions');
            $table->foreign('debit_card_transaction_id')->references('id')->on('debit_card_transactions');
            $table->foreign('provider_transaction_id')->references('id')->on('provider_transactions')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('person_id')->references('id')->on('people')->onUpdate('cascade');
            $table->foreign('currency_code')->references('code')->on('currencies');
            $table->foreign('category_id')->references('id')->on('debit_transaction_categories')->onUpdate('cascade');
            $table->foreign('state_code')->references('reference_code')->on('states')->onDelete('restrict')->onUpdate('cascade');

            $table->index('debit_account_id');
            $table->index('debit_transaction_id');
            $table->index('debit_card_transaction_id');
            $table->index('provider_transaction_id');
            $table->index('person_id');
            $table->index('state_code');
            $table->index('category_id');
            $table->index(['id', 'debit_account_id'], 'id_debit_account_id_index');
            $table->index('ledger_block_uuid');
            $table->index('parent_id');
            $table->index('original_reference_number');
            $table->index('cto_card_transaction_uuid');
            $table->index('authorized_at');
        });

        // Generated column: type derived from amount sign
        DB::statement("
            ALTER TABLE processing_card_transactions
            ADD COLUMN type VARCHAR(10) GENERATED ALWAYS AS (
                IF(amount < 0, 'debit', 'credit')
            ) STORED,
            ADD INDEX type_index (type)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_card_transactions');
    }
};
