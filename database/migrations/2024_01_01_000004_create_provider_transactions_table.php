<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('debit_transaction_id')->nullable();
            $table->string('external_id');
            $table->string('secondary_external_id')->nullable();
            $table->string('provider'); // nium, dbs, neo, mastercard
            $table->boolean('payout_service_enabled')->default(false);
            $table->json('payload')->nullable();
            $table->string('state_code')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id'], 'provider_external_id_unique');

            $table->foreign('debit_transaction_id')->references('id')->on('debit_transactions');
            $table->foreign('state_code')->references('reference_code')->on('states')->onUpdate('cascade');

            $table->index('debit_transaction_id');
            $table->index('external_id');
            $table->index('created_at');
            $table->index('state_code');
            $table->index('secondary_external_id', 'index_secondary_external_id');
            $table->index(['secondary_external_id', 'provider'], 'index_secondary_external_id_provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_transactions');
    }
};
