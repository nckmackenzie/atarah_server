<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use HasFactory,HasUuids,SoftDeletes;
    
    protected $keyType = 'string';
    public $timestamps = false;
    protected $hidden = ['deleted_at'];
    protected $perPage = 10;

    protected $guarded = ['id','created_at','deleted_at'];

    protected $casts = [
        'active' => 'boolean',
    ];
}
