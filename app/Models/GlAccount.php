<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class GlAccount extends Model
{
    public $timestamps = false;
    protected $guarded = ['id', 'created_at', 'deleted_at'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
        'is_subcategory' => 'boolean',
        'account_type_id' => 'string',
        'parent_id' => 'string',
        'is_bank' => 'boolean',
        'active' => 'boolean',
        'is_editable' => 'boolean',
    ];

    public function children()
    {
        return $this->hasMany(GlAccount::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function accountType()
    {
        return $this->belongsTo(GlAccount::class, 'account_type_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'gl_account_id');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords($value),
            set: fn (string $value) => strtolower(trim($value)),
        );
    }
}
