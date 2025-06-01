<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Role extends BaseModel
{
    protected $fillable = [
        'name',
        'is_active',
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rights()
    {
        return $this->hasMany(RoleRight::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => strtolower($value),
        );
    }
}
