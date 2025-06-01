<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Client extends BaseModel
{
    protected $table = 'clients';
    protected $guarded = ['id','created_at', 'deleted_at'];

    protected $hidden = ['opening_balance', 'opening_balance_date','deleted_at'];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
             set: fn (string $value) => strtolower(trim($value)),
        );
    }

    protected function address(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $value ? ucwords($value) : null,
            set: fn (string|null $value) => $value ? strtolower(trim($value)) : null,
        );
    }

    protected function taxPin(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $value ? strtoupper($value) : null,
            set: fn (string|null $value) => $value ? strtolower(trim($value)) : null,
        );
    }
}
