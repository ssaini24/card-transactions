<?php

namespace App\Services;

use App\Models\DebitAccount;
use App\Repositories\CardTransactionRepository;

class CardTransactionService
{
    public function __construct(
        private readonly CardTransactionRepository $repository,
    ) {}

    public function getTransactionsForAccount(DebitAccount $debitAccount): array
    {
        // TODO: implement
        return [];
    }
}
