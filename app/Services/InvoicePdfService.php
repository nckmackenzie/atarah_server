<?php

namespace App\Services;

use App\Models\InvoiceHeader;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    public function generatePdf(InvoiceHeader $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['client', 'details.service']);
        
        $data = [
            'invoice' => $invoice,
            'company' => $this->getCompanyDetails(),
            'subtotal' => $invoice->sub_total,
            'vatAmount' => $invoice->vat_amount,
            'total' => $invoice->total_amount,
            'balance' => $invoice->total_amount - ($invoice->amount_paid ?? 0),
        ];

        return Pdf::loadView('invoices.template', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isRemoteEnabled' => true,
                      'isHtml5ParserEnabled' => true,
                  ]);
    }

    public function generateAndDownload(InvoiceHeader $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generatePdf($invoice);
        $filename = "invoice-{$invoice->invoice_no}.pdf";
        
        return $pdf->download($filename);
    }

    public function generateAndStream(InvoiceHeader $invoice): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generatePdf($invoice);
        $filename = "invoice-{$invoice->invoice_no}.pdf";
        
        return $pdf->stream($filename);
    }

    private function getCompanyDetails(): array
    {
        return [
            'name' => config('app.company_name', 'Your Company Name'),
            'address' => config('app.company_address', 'Your Company Address'),
            'phone' => config('app.company_phone', '+1234567890'),
            'email' => config('app.company_email', 'info@company.com'),
            'website' => config('app.url'),
            'logo' => public_path('images/logo.png'), 
        ];
    }
}