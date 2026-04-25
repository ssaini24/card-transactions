<?php

namespace App\Services;

use App\Models\DebitAccount;
use App\Models\ProcessingCardTransaction;
use App\Repositories\CardTransactionRepository;

class CardTransactionService
{
    public function __construct(
        private readonly CardTransactionRepository $repository,
    ) {}

    /**
     * Get a summary of all transactions for a debit account.
     */
    public function getTransactionsForAccount(DebitAccount $debitAccount): array
    {
        $transactions = ProcessingCardTransaction::where('debit_account_id', $debitAccount->id)->get();

        $result = [];
        foreach ($transactions as $transaction) {
            $result[] = [
                'id'            => $transaction->id,
                'amount'        => $transaction->amount,
                'state'         => $transaction->state_code,
                'card_last_four' => $transaction->debitCardTransaction->debitCard->last_four,
                'person_name'   => $transaction->person->full_name,
                'category'      => $transaction->category?->name,
            ];
        }

        return $result;
    }
}
