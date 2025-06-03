<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Service extends BaseModel
{
    protected $guarded = ['id','created_at','deleted_at'];
    protected $hidden = ['gl_account_id','deleted_at'];
    protected $casts = [
        'rate' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(GLAccount::class, 'gl_account_id');
    }

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
