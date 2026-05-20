<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate_to_usd',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'currency_code', 'code');
    }
}
