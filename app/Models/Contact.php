<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'description',
    ];

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}
