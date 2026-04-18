<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DebitCard extends Model
{
    public const PROVIDER_NIUM = 'nium';
    public const PROVIDER_EPISODE_SIX = 'episode_six';

    protected $fillable = ['uuid', 'debit_account_id', 'provider', 'last_four'];

    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(DebitAccount::class);
    }

    public function debitCardTransactions(): HasMany
    {
        return $this->hasMany(DebitCardTransaction::class);
    }

    public function isEpisodeSix(): bool
    {
        return $this->provider === self::PROVIDER_EPISODE_SIX;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }
}
