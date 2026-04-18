<?php

namespace App\Repositories;

use App\Models\DebitCardTransaction;
use App\Models\ProcessingCardTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class CardTransactionRepository
{
    public function getProcessingTransactionsForAccount(int $debitAccountId): Collection
    {
        // TODO: implement
        return collect();
    }

    public function getPendingPrefundTransactions(string $provider): LazyCollection
    {
        // TODO: implement
        return LazyCollection::empty();
    }

    public function getSettledTransactions(int $debitAccountId): Collection
    {
        // TODO: implement
        return collect();
    }
}
