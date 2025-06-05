<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $hidden = ['service_id', 'header_id'];
    protected $guarded = ['id'];
    public $timestamps = false;

    public function header()
    {
        return $this->belongsTo(InvoiceHeader::class, 'header_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
