<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProviderTransaction extends Model
{
    public const PROVIDER_NIUM = 'nium';
    public const PROVIDER_DBS = 'dbs';
    public const PROVIDER_NEO = 'neo';
    public const PROVIDER_MASTERCARD = 'mastercard';

    public const STATE_PENDING = 'PTPN';
    public const STATE_ACCEPTED = 'PTAC';
    public const STATE_DECLINED = 'PTDC';
    public const STATE_ON_HOLD = 'PTHO';
    public const STATE_IN_PROGRESS = 'PTIN';

    protected $fillable = [
        'debit_transaction_id', 'external_id', 'secondary_external_id',
        'provider', 'payout_service_enabled', 'payload', 'state_code',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'payout_service_enabled' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function debitTransaction(): BelongsTo
    {
        return $this->belongsTo(DebitTransaction::class);
    }

    public function processingCardTransaction(): HasOne
    {
        return $this->hasOne(ProcessingCardTransaction::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeOfProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('state_code', self::STATE_PENDING);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('state_code', self::STATE_ACCEPTED);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isNiumProvider(): bool
    {
        return $this->provider === self::PROVIDER_NIUM;
    }

    public function isDBSProvider(): bool
    {
        return $this->provider === self::PROVIDER_DBS;
    }

    public function markAsAccepted(): bool
    {
        return $this->update(['state_code' => self::STATE_ACCEPTED]);
    }

    public function markAsDeclined(): bool
    {
        return $this->update(['state_code' => self::STATE_DECLINED]);
    }

    public function markOnHold(): bool
    {
        return $this->update(['state_code' => self::STATE_ON_HOLD]);
    }

    public function getProviderReferenceId(): ?string
    {
        return data_get($this->payload, 'reference_id');
    }

    public function transactionMerchantName(): ?string
    {
        return data_get($this->payload, 'merchant.name');
    }

    public function transactionCurrencyCode(): ?string
    {
        return data_get($this->payload, 'currency_code');
    }
}
