<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceHeader extends BaseModel
{
    protected $hidden = ['client_id','created_by','deleted_at'];
    public $timestamps = true;

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',        
        'total_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];
    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'header_id');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePaymentDetail::class, 'invoice_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function scopeFullyPaid($query)
    {
        return $query->whereRaw('
            total_amount <= COALESCE((
                SELECT SUM(amount) 
                FROM invoice_payment_details 
                WHERE invoice_id = invoice_headers.id
            ), 0)
        ');
    }

    public function scopeNotFullyPaid($query)
    {
        return $query->whereRaw('
            total_amount > COALESCE((
                SELECT SUM(amount) 
                FROM invoice_payment_details 
                WHERE invoice_id = invoice_headers.id
            ), 0)
        ');
    }

    public function scopeOverdue($query)
    {
        return $query->whereDate('due_date', '<', now())
                           ->whereRaw('
                               total_amount > COALESCE((
                                   SELECT SUM(amount) 
                                   FROM invoice_payment_details 
                                   WHERE invoice_id = invoice_headers.id
                               ), 0)
                           ');
    }
}
