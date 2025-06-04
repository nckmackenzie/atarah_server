<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ledger extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $hidden = ['deleted_at'];
    protected $guarded = ['id','created_at','deleted_at'];
    
    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'is_journal' => 'boolean',
        'journal_no' => 'integer',
    ];

    protected function account(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    protected function parentaccount(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $value ? ucwords($value) : null,
            set: fn (string|null $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    protected function reference(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $value ? ucwords($value) : null,
            set: fn (string|null $value) => $value ? strtolower(trim($value)) : null,
        );
    }
}
