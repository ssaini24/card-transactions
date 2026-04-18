<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->string('reference_code', 10)->primary();
            $table->string('name');
            $table->string('model_type')->nullable();
            $table->timestamps();
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::create('debit_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type')->default('current'); // current, savings
            $table->string('currency_code', 10);
            $table->bigInteger('balance')->default(0);
            $table->timestamps();

            $table->foreign('currency_code')->references('code')->on('currencies');
        });

        Schema::create('debit_cards', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('debit_account_id');
            $table->string('provider')->default('nium'); // nium, episode_six
            $table->string('last_four', 4)->nullable();
            $table->timestamps();

            $table->foreign('debit_account_id')->references('id')->on('debit_accounts');
        });

        Schema::create('debit_transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Seed reference data
        DB::table('currencies')->insert([
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'USD', 'name' => 'United States Dollar', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('states')->insert([
            // DebitTransaction states
            ['reference_code' => 'DTCR', 'name' => 'CREATED',  'model_type' => 'DebitTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'DTST', 'name' => 'SETTLED',  'model_type' => 'DebitTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'DTFL', 'name' => 'FAILED',   'model_type' => 'DebitTransaction', 'created_at' => now(), 'updated_at' => now()],
            // DebitCardTransaction states
            ['reference_code' => 'DTPD', 'name' => 'PENDING',  'model_type' => 'DebitCardTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'DTAC', 'name' => 'ACCEPTED', 'model_type' => 'DebitCardTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'DTRJ', 'name' => 'REJECTED', 'model_type' => 'DebitCardTransaction', 'created_at' => now(), 'updated_at' => now()],
            // ProviderTransaction states
            ['reference_code' => 'PTPN', 'name' => 'PENDING',  'model_type' => 'ProviderTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'PTAC', 'name' => 'ACCEPTED', 'model_type' => 'ProviderTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'PTDC', 'name' => 'DECLINED', 'model_type' => 'ProviderTransaction', 'created_at' => now(), 'updated_at' => now()],
            // ProcessingCardTransaction states
            ['reference_code' => 'PCAU', 'name' => 'AUTHORIZE', 'model_type' => 'ProcessingCardTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'PCCL', 'name' => 'CLEAR',     'model_type' => 'ProcessingCardTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'PCST', 'name' => 'SETTLE',    'model_type' => 'ProcessingCardTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'PCRF', 'name' => 'REFUND',    'model_type' => 'ProcessingCardTransaction', 'created_at' => now(), 'updated_at' => now()],
            ['reference_code' => 'PCRV', 'name' => 'REVERSE',   'model_type' => 'ProcessingCardTransaction', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_transaction_categories');
        Schema::dropIfExists('debit_cards');
        Schema::dropIfExists('debit_accounts');
        Schema::dropIfExists('people');
        Schema::dropIfExists('states');
        Schema::dropIfExists('currencies');
    }
};
