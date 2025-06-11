<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseAttachment extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];
    protected $hidden = ['expense_id'];
}
