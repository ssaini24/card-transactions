<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DebitAccount extends Model
{
    public const TYPE_CURRENT = 'current';
    public const TYPE_SAVINGS = 'savings';

    protected $fillable = ['uuid', 'type', 'currency_code', 'balance'];

    protected function casts(): array
    {
        return ['balance' => 'integer'];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function debitCards(): HasMany
    {
        return $this->hasMany(DebitCard::class);
    }

    public function debitTransactions(): HasMany
    {
        return $this->hasMany(DebitTransaction::class);
    }

    public function processingCardTransactions(): HasMany
    {
        return $this->hasMany(ProcessingCardTransaction::class);
    }
}
