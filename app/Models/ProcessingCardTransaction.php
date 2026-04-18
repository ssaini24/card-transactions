<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessingCardTransaction extends Model
{
    use SoftDeletes;

    public const STATE_AUTHORIZE = 'PCAU';
    public const STATE_CLEAR = 'PCCL';
    public const STATE_SETTLE = 'PCST';
    public const STATE_REFUND = 'PCRF';
    public const STATE_REVERSE = 'PCRV';
    public const STATE_CLEAR_DUE = 'PCCD';
    public const STATE_CLEAR_OVERDUE = 'PCCO';

    protected $fillable = [
        'uuid', 'code', 'cto_card_transaction_uuid', 'debit_account_id',
        'debit_transaction_id', 'debit_card_transaction_id', 'provider_transaction_id',
        'person_id', 'ledger_block_uuid', 'release_timeout_days', 'release_timeout_hours',
        'parent_id', 'currency_code', 'amount', 'amount_refunded',
        'remaining_repayment_amount', 'fx_amount_paid', 'counterparty_name',
        'counterparty_code', 'counterparty_bank_code', 'description', 'note',
        'category_id', 'exchange_rate', 'original_reference_number',
        'acquirer_reference_number', 'state_code', 'is_receivable',
        'authorized_at', 'realized_at', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'amount_refunded' => 'integer',
            'remaining_repayment_amount' => 'integer',
            'fx_amount_paid' => 'integer',
            'exchange_rate' => 'decimal:6',
            'is_receivable' => 'boolean',
            'authorized_at' => 'datetime',
            'realized_at' => 'datetime',
            'settled_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(DebitAccount::class);
    }

    public function debitTransaction(): BelongsTo
    {
        return $this->belongsTo(DebitTransaction::class);
    }

    public function debitCardTransaction(): BelongsTo
    {
        return $this->belongsTo(DebitCardTransaction::class);
    }

    public function providerTransaction(): BelongsTo
    {
        return $this->belongsTo(ProviderTransaction::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DebitTransactionCategory::class);
    }

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(ProcessingCardTransaction::class, 'parent_id');
    }

    public function childTransactions(): HasMany
    {
        return $this->hasMany(ProcessingCardTransaction::class, 'parent_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeAuthorize(Builder $query): Builder
    {
        return $query->where('state_code', self::STATE_AUTHORIZE);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->whereIn('state_code', [self::STATE_AUTHORIZE]);
    }

    public function scopeProcessingAndNotRepaid(Builder $query): Builder
    {
        return $query->whereIn('state_code', [
            self::STATE_AUTHORIZE,
            self::STATE_CLEAR_DUE,
            self::STATE_CLEAR_OVERDUE,
        ]);
    }

    public function scopeForDebitAccount(Builder $query, int $debitAccountId): Builder
    {
        return $query->where('debit_account_id', $debitAccountId);
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
              ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$keyword}%"))
              ->orWhereHas('person', fn ($p) => $p->where('full_name', 'like', "%{$keyword}%"))
              ->orWhereHas('debitCardTransaction.debitCard', fn ($dc) => $dc->where('last_four', 'like', "%{$keyword}%"));
        });
    }

    public function scopeOrderByMostRecent(Builder $query): Builder
    {
        return $query->orderBy('authorized_at', 'desc');
    }

    // ── Static queries ─────────────────────────────────────────────────────────

    public static function getAuthorizedAmountForAccount(int $debitAccountId): int
    {
        return (int) static::where('debit_account_id', $debitAccountId)
            ->where('state_code', self::STATE_AUTHORIZE)
            ->sum('amount');
    }
}
