<?php

namespace App\Models;


class InvoicePaymentHeader extends BaseModel
{
    protected $hidden = ['client_id'];

    public function details()
    {
        return $this->hasMany(InvoicePaymentDetail::class, 'header_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
