<?php

namespace App\Repositories;

use App\Models\DebitCardTransaction;
use App\Models\ProcessingCardTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class CardTransactionRepository
{
    /**
     * Get processing transactions for a debit account.
     * Joins debit_card_transactions to include card details.
     */
    public function getProcessingTransactionsForAccount(int $debitAccountId): Collection
    {
        return ProcessingCardTransaction::join(
            'debit_card_transactions',
            'processing_card_transactions.debit_transaction_id',
            '=',
            'debit_card_transactions.debit_transaction_id'
        )
        ->where('state_code', ProcessingCardTransaction::STATE_AUTHORIZE)
        ->get();
    }

    /**
     * Get all pending prefund transactions for a provider
     * that were transferred in the last 7 days.
     */
    public function getPendingPrefundTransactions(string $provider): LazyCollection
    {
        return DebitCardTransaction::whereRaw('DATE(transferred_to_prefund_at) >= ?', [
            now()->subDays(7)->toDateString(),
        ])
        ->where('prefund_provider', $provider)
        ->cursor();
    }

    /**
     * Get all settled transactions for an account.
     */
    public function getSettledTransactions(int $debitAccountId): Collection
    {
        return ProcessingCardTransaction::select('*')
            ->where('debit_account_id', $debitAccountId)
            ->where('state_code', ProcessingCardTransaction::STATE_SETTLE)
            ->get();
    }
}
