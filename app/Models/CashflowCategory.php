<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashflowCategory extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'cashflow_category_id')->orderBy('name');
    }
}
