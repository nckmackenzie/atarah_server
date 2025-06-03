<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Project extends BaseModel
{
    protected function name(): Attribute
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
}
