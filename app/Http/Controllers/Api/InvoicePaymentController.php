<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoicePaymentRequest;
use App\Models\Client;
use App\Models\InvoiceHeader;
use App\Models\InvoicePaymentDetail;
use App\Models\InvoicePaymentHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InvoicePaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $query = InvoicePaymentDetail::query()
                    ->join('invoice_payment_headers as h', 'invoice_payment_details.header_id', '=', 'h.id')
                    ->join('invoice_headers as i', 'invoice_payment_details.invoice_id', '=', 'i.id')
                    ->join('clients as c', 'i.client_id', '=', 'c.id')
                    ->select([
                        'invoice_payment_details.*',
                        'h.payment_date',
                        'h.payment_method', 
                        'h.payment_reference',
                        'i.invoice_no',
                        'i.invoice_date',
                        'i.total_amount',
                        'c.name as client_name'
                    ]);
        
            if ($search = $request->input('q')) {
                $query->where(function ($q) use ($search) {
                    $q->where('c.name', 'like', "%{$search}%")
                    ->orWhere('i.invoice_no', 'like', "%{$search}%")
                    ->orWhere('h.payment_reference', 'like', "%{$search}%");
                });
            }
        
        $payments = $query->orderBy('h.payment_date', 'desc')->get();
        return response()->json(['data' => $payments], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoicePaymentRequest $request,InvoiceHeader $invoice)
    {
        return $this->handleCreate($request, $invoice);
    }

    /**
     * Store a newly created resource in storage(Bulk).
     */
    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_date' => 'required|date|before_or_equal:tomorrow',
            'payment_method' => 'required|string|max:255|in:cash,mpesa,cheque',
            'payment_reference' => 'required|string|max:255',
            'invoices' => 'required|array',
            'invoices.*.invoice_id' => 'required|exists:invoice_headers,id',
            'invoices.*.amount' => 'required|numeric|min:0',
        ]);

        $invoicesGreaterThanZero = collect($validated['invoices'])->filter(function ($invoice) {
            return floatval($invoice['amount']) > 0;
        });
        if ($invoicesGreaterThanZero->isEmpty()) {
           return response()->json(['error' => 'At least one invoice must have a payment amount greater than zero.'],400);
        }

        foreach ($invoicesGreaterThanZero as $paymentData) {
            $invoice = InvoiceHeader::findOrFail($paymentData['invoice_id']);
            $totalPayments = $invoice->payments()->sum('amount');
            if($invoice->client_id != $validated['client_id']) {
                throw ValidationException::withMessages([
                    'client_id' => 'Client does not match the invoice client.'
                ]);
            }
            if(Carbon::parse($validated['payment_date']) < Carbon::parse($invoice->invoice_date)){
                throw ValidationException::withMessages([
                    'payment_date' => 'Payment date cannot be before the invoice date.'
                ]);
            }
            if(floatval($paymentData['amount']) > ($invoice->total_amount - $totalPayments)){
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount cannot be greater than the balance.'
                ]);
            }            
        }

        try {            

            DB::transaction(function() use($validated,$invoicesGreaterThanZero) {

                $header = InvoicePaymentHeader::create([
                                'payment_date' => formatDate($validated['payment_date']),
                                'client_id' => $validated['client_id'],
                                'amount' => array_sum(array_column($validated['invoices'], 'amount')),
                                'payment_method' => $validated['payment_method'],
                                'payment_reference' => $validated['payment_reference'],
                            ]);

                foreach ($invoicesGreaterThanZero as $paymentData) {
                    $invoice = InvoiceHeader::find($paymentData['invoice_id']);
                    $detail = InvoicePaymentDetail::create([
                        'header_id' => $header->id,
                        'invoice_id' => $paymentData['invoice_id'],
                        'amount' => $paymentData['amount'],
                    ]);

                    $this->addToLedgerMin($validated['payment_date'], 'cash at bank', $paymentData['amount'],
                                          0, "invoice payment for invoice #{$invoice->invoice_no}", 
                                          $validated['payment_reference'], 'invoice_payment', $detail->id);

                    $this->addToLedgerMin($validated['payment_date'], 'accounts receivable', 0,
                                          $paymentData['amount'], "invoice payment for invoice #{$invoice->invoice_no}", 
                                          $validated['payment_reference'], 'invoice_payment', $detail->id);
                }
            });
            
        } catch (\Exception $e) {
            Log::error('Error creating bulk payments: ' . $e->getMessage());
            return response()->json(['error' => 'Error creating payments'], 500);
        }

        return response()->json(['message' => 'Payments created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function fetchPendingClientInvoices(string $clientId)
    {
        $invoices = InvoiceHeader::where('client_id', $clientId)
                        ->select(['id', 'invoice_no', 'total_amount', 'invoice_date',
                                          DB::raw('total_amount - (SELECT COALESCE(SUM(amount),0) FROM invoice_payment_details WHERE invoice_id = invoice_headers.id) as balance')])
                        ->havingRaw('balance > 0')
                        ->get();

        return response()->json(['data' => $invoices], 200);
    }

    private function handleCreate(InvoicePaymentRequest $request, InvoiceHeader $invoice)
    {
        $validated = $request->validated();

        $paidAmount = $invoice->payments()->sum('amount');
        $remainingAmount = $invoice->total_amount - $paidAmount;

        if(Carbon::parse($validated['payment_date']) < Carbon::parse($invoice->invoice_date)){
            throw ValidationException::withMessages([
                'payment_date' => 'Payment date cannot be before the invoice date.'
            ]);
        }
        if(floatval($validated['amount']) > $remainingAmount){
            throw ValidationException::withMessages([
                'amount' => 'Payment amount cannot be greater than the balance.'
            ]);
        }

        try {
           
            DB::transaction(function() use($validated,$invoice){
                $header = InvoicePaymentHeader::create([
                    'payment_date' => formatDate($validated['payment_date']),
                    'client_id' => $invoice->client_id,
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'payment_reference' => $validated['payment_reference'],
                ]);

                InvoicePaymentDetail::create([
                    'header_id' => $header->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $validated['amount'],
                ]);

                $this->addToLedgerMin($validated['payment_date'],'cash at bank',$validated['amount'],
                                        0, "invoice payment for invoice #{$invoice->invoice_no}",$validated['payment_reference'],'invoice_payment',$header->id);
                 
                $this->addToLedgerMin($validated['payment_date'],'accounts receivable',0,
                                        $validated['amount'], "invoice payment for invoice #{$invoice->invoice_no}",$validated['payment_reference'],'invoice_payment',$header->id);
            });

            return response()->json(['message' => 'Payment created successfully'], 201);

        } catch (\Exception $e) {
            Log::error('Error creating payment for invoice ID ' . $e->getMessage());
            return response()->json(['error' => 'Error creating payment for invoice'], 500);
        } 
    }
}
