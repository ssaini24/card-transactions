<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DebitTransactionCategory extends Model
{
    protected $fillable = ['uuid', 'name'];

    public function debitTransactions(): HasMany
    {
        return $this->hasMany(DebitTransaction::class, 'category_id');
    }

    public function processingCardTransactions(): HasMany
    {
        return $this->hasMany(ProcessingCardTransaction::class, 'category_id');
    }
}
