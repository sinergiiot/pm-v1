<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekeningBank extends Model
{
    protected $fillable = [
        'bank_name',
        'account_number',
        'account_name',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'rekening_bank_id');
    }
}
