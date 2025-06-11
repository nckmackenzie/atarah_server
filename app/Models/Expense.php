<?php

namespace App\Models;


class Expense extends BaseModel
{
    protected $casts = [
        'id' => 'string',
        'expense_date' => 'date',
        'expense_no' => 'integer',
    ];

    public function details()
    {
        return $this->hasMany(ExpenseDetail::class, 'expense_id');
    }

    public function attachments()
    {
        return $this->hasMany(ExpenseAttachment::class, 'expense_id');
    }
}
