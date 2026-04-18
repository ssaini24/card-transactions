<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = ['full_name', 'email'];

    public function debitTransactions(): HasMany
    {
        return $this->hasMany(DebitTransaction::class);
    }

    public function processingCardTransactions(): HasMany
    {
        return $this->hasMany(ProcessingCardTransaction::class);
    }
}
