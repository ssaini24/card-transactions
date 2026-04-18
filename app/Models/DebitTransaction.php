<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DebitTransaction extends Model
{
    public const KIND_PARENT = 'parent';
    public const KIND_FEE = 'fee';
    public const KIND_ROLLBACK = 'rollback';

    protected $fillable = [
        'uuid', 'code', 'debit_account_id', 'person_id', 'currency_code',
        'amount', 'counterparty_name', 'counterparty_code', 'counterparty_bank_code',
        'counterparty_id', 'counterparty_transaction_id', 'description', 'note',
        'category_id', 'kind', 'parent_uuid', 'is_intrabank', 'realized_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'is_intrabank' => 'boolean',
            'realized_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(DebitAccount::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DebitTransactionCategory::class);
    }

    public function providerTransaction(): HasOne
    {
        return $this->hasOne(ProviderTransaction::class);
    }

    public function providerTransactions(): HasMany
    {
        return $this->hasMany(ProviderTransaction::class);
    }

    public function debitCardTransaction(): HasOne
    {
        return $this->hasOne(DebitCardTransaction::class);
    }

    public function processingCardTransaction(): HasOne
    {
        return $this->hasOne(ProcessingCardTransaction::class);
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(DebitTransaction::class, 'parent_uuid', 'uuid');
    }

    public function childrenTransactions(): HasMany
    {
        return $this->hasMany(DebitTransaction::class, 'parent_uuid', 'uuid');
    }

    public function feeTransactions(): HasMany
    {
        return $this->hasMany(DebitTransaction::class, 'parent_uuid', 'uuid')
            ->where('kind', self::KIND_FEE);
    }

    public function rollbackTransactions(): HasMany
    {
        return $this->hasMany(DebitTransaction::class, 'parent_uuid', 'uuid')
            ->where('kind', self::KIND_ROLLBACK);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOfDebitAccount(Builder $query, int $debitAccountId): Builder
    {
        return $query->where('debit_account_id', $debitAccountId);
    }

    public function scopeExcludeFeeTransactions(Builder $query): Builder
    {
        return $query->where('kind', '!=', self::KIND_FEE);
    }

    public function scopeOfPerson(Builder $query, int $personId): Builder
    {
        return $query->where('person_id', $personId);
    }

    public function scopeOfSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('code', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('counterparty_name', 'like', "%{$keyword}%")
              ->orWhereHas('person', fn ($p) => $p->where('full_name', 'like', "%{$keyword}%"))
              ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$keyword}%"));
        });
    }

    public function scopeChildOf(Builder $query, string $parentUuid): Builder
    {
        return $query->where('parent_uuid', $parentUuid);
    }
}
