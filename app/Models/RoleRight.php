<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleRight extends Model
{
    public $timestamps = false;

    protected $fillable = ['role_id','form_id'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
