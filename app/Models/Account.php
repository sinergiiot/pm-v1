<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    public const TYPE_DEBIT = 'debit';

    public const TYPE_CREDIT = 'credit';

    protected $fillable = [
        'name',
        'type',
        'pnl_category_id',
        'cashflow_category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pnlCategory(): BelongsTo
    {
        return $this->belongsTo(PnlCategory::class, 'pnl_category_id');
    }

    public function cashflowCategory(): BelongsTo
    {
        return $this->belongsTo(CashflowCategory::class, 'cashflow_category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
