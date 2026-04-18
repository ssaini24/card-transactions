<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->nullable()->unique();
            $table->string('external_number');
            $table->unsignedBigInteger('debit_card_id');
            $table->unsignedBigInteger('debit_transaction_id')->nullable();
            $table->string('state_code')->nullable();
            $table->string('provider')->default('nium');
            $table->string('type');
            $table->bigInteger('amount');
            $table->string('currency', 10);
            $table->string('merchant_category_code')->nullable();
            $table->json('payload');
            $table->string('prefund_provider')->nullable();
            $table->timestamp('transferred_to_prefund_at')->nullable();
            $table->timestamps();

            $table->foreign('debit_card_id')->references('id')->on('debit_cards')->onUpdate('cascade');
            $table->foreign('debit_transaction_id')->references('id')->on('debit_transactions')->onUpdate('cascade');
            $table->foreign('state_code')->references('reference_code')->on('states')->onUpdate('cascade');

            $table->index('debit_card_id');
            $table->index('debit_transaction_id');
            $table->index('state_code');
            $table->index('type');
            $table->index('external_number');
            $table->index('provider');
            $table->index('transferred_to_prefund_at', 'idx_transferred_to_prefund_at');
            $table->index(['type', 'state_code', 'created_at'], 'created_since');
        });

        // Add generated column for origin card transaction (reversal linking)
        DB::statement("
            ALTER TABLE debit_card_transactions
            ADD COLUMN origin_debit_card_transaction_id INT UNSIGNED GENERATED ALWAYS AS (
                CASE WHEN type IN ('REVERSAL', 'PARTIAL_REVERSAL', 'REVERSAL_ADVICE', 'Settlement_Direct_Debit')
                     AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.original_debit_card_transaction_id')) <> 'null'
                THEN JSON_UNQUOTE(JSON_EXTRACT(payload, '$.original_debit_card_transaction_id'))
                ELSE NULL END
            ) VIRTUAL,
            ADD INDEX origin_debit_card_transaction_id_index (origin_debit_card_transaction_id)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_card_transactions');
    }
};
