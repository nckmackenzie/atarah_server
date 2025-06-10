<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InvoicePaymentDetail extends Model
{

    use HasUuids;
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $hidden = [
        'header_id',
        'invoice_id',
    ];

    public function header()
    {
        return $this->belongsTo(InvoicePaymentHeader::class, 'header_id');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoiceHeader::class, 'invoice_id');
    }
}
