<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $casts = [
        'active' => 'boolean',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords(trim($value)),
            set: fn (string $value) => strtolower($value),
        );
    }
}
