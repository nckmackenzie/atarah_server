<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePaymentDetail extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function header()
    {
        return $this->belongsTo(InvoicePaymentHeader::class, 'header_id');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoiceHeader::class, 'invoice_id');
    }
}
