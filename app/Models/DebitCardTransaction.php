<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DebitCardTransaction extends Model
{
    public const STATE_PENDING = 'DTPD';
    public const STATE_ACCEPTED = 'DTAC';
    public const STATE_REJECTED = 'DTRJ';
    public const STATE_INTERNAL_TRANSFER_SUCCESS = 'DTIS';

    public const TYPE_DEBIT = 'DEBIT';
    public const TYPE_CREDIT = 'CREDIT';
    public const TYPE_REVERSAL = 'REVERSAL';
    public const TYPE_PARTIAL_REVERSAL = 'PARTIAL_REVERSAL';
    public const TYPE_REVERSAL_ADVICE = 'REVERSAL_ADVICE';
    public const TYPE_SETTLEMENT_CREDIT = 'Settlement_Credit';
    public const TYPE_SETTLEMENT_DIRECT_DEBIT = 'Settlement_Direct_Debit';

    public const DEBIT_TYPES = [self::TYPE_DEBIT, self::TYPE_SETTLEMENT_DIRECT_DEBIT];
    public const REVERSAL_TYPES = [self::TYPE_REVERSAL, self::TYPE_PARTIAL_REVERSAL, self::TYPE_REVERSAL_ADVICE];
    public const COMPLETED_STATES = [self::STATE_ACCEPTED, self::STATE_INTERNAL_TRANSFER_SUCCESS];

    protected $fillable = [
        'reference_code', 'external_number', 'debit_card_id', 'debit_transaction_id',
        'state_code', 'provider', 'type', 'amount', 'currency',
        'merchant_category_code', 'payload', 'prefund_provider', 'transferred_to_prefund_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payload' => 'array',
            'transferred_to_prefund_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function debitCard(): BelongsTo
    {
        return $this->belongsTo(DebitCard::class);
    }

    public function debitTransaction(): BelongsTo
    {
        return $this->belongsTo(DebitTransaction::class);
    }

    public function processingCardTransaction(): HasOne
    {
        return $this->hasOne(ProcessingCardTransaction::class);
    }

    public function providerTransactions(): HasMany
    {
        return $this->hasMany(ProviderTransaction::class, 'debit_transaction_id', 'debit_transaction_id');
    }

    public function originDebitCardTransaction(): BelongsTo
    {
        return $this->belongsTo(DebitCardTransaction::class, 'origin_debit_card_transaction_id');
    }

    public function reversalCardTransaction(): HasOne
    {
        return $this->hasOne(DebitCardTransaction::class, 'origin_debit_card_transaction_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOfDebitCard(Builder $query, int $debitCardId): Builder
    {
        return $query->where('debit_card_id', $debitCardId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('state_code', self::STATE_PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('state_code', self::COMPLETED_STATES);
    }

    public function scopeOfProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isDebitTransaction(): bool
    {
        return in_array($this->type, self::DEBIT_TYPES);
    }

    public function isReversalTransaction(): bool
    {
        return in_array($this->type, self::REVERSAL_TYPES);
    }

    public function getFees(): ?array
    {
        return data_get($this->payload, 'fees');
    }

    public function getExchangeRate(): ?float
    {
        return data_get($this->payload, 'exchange_rate');
    }
}
