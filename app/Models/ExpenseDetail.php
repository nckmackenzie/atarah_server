<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseDetail extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $hidden = ['expense_id', 'project_id', 'gl_account_id'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    
    public function account()
    {
        return $this->belongsTo(GlAccount::class, 'gl_account_id');
    }

}
