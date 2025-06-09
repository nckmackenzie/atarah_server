<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\InvoiceHeader;
use App\Models\Ledger;
use App\Models\Service;
use App\Services\InvoicePdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function Termwind\parse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InvoiceHeader::with(['client:id,name'])
                ->withSum('payments as amount_paid','amount');
        if($search = $request->input('q')){
            $query = $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('invoice_date', 'like', "%{$search}%")
                    ->orWhere('total_amount', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        if($status = $request->input('status')){
            if($status === 'paid') {
                $query = $query->fullyPaid();
            } elseif($status === 'overdue') {
                $query = $query->overdue()->notFullyPaid();
            } elseif($status === 'pending') {
                $query = $query->notFullyPaid();
            }
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->limit(100)->get();                

        return response()->json(['data' => $invoices]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoiceRequest $request)
    {
        return $this->handleCreateUpdate($request, new InvoiceHeader());
    }

    /**
     * Display the specified resource.
     */
    public function show(InvoiceHeader $invoice)
    {
        $invoice->load(['client:id,name', 'details.service:id,name']);
        $invoice->loadSum('payments as amount_paid', 'amount');
        return response()->json(['data' => $invoice]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoiceRequest $request, InvoiceHeader $invoice)
    {
        return $this->handleCreateUpdate($request, $invoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvoiceHeader $invoice)
    {
        $hasPayments = $invoice->payments()->exists();
        
        if ($hasPayments) {
            return response()->json(['error' => 'Cannot delete invoice with payments.'], 400);
        }

        try {
            DB::transaction(function() use($invoice) {
                $invoice->details()->delete();
                Ledger::where('transaction_type', 'invoice')
                        ->where('transaction_id', $invoice->id)
                        ->delete();
                $invoice->delete();
            });
            return response()->json(['message' => 'Invoice deleted successfully.'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting invoice: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while deleting the invoice.'], 500);
        }
    }

    public function generatePdf(InvoiceHeader $invoice)
    {
        try {
           
            $invoice->load(['client', 'details.service']);
            $invoice->loadSum('payments as amount_paid', 'amount');

            $data = [
                'invoice' => $invoice,
                'subtotal' => $invoice->sub_total,
                'vatAmount' => $invoice->vat_amount,
                'total' => $invoice->total_amount,
                'balance' => floatval($invoice->total_amount) - floatval($invoice->amount_paid ?? 0),
            ];

            $pdf = Pdf::loadView('invoices.template', $data);

            $filename = "invoice-{$invoice->invoice_no}-" . now()->format('Y-m-d-H-i-s') . '.pdf';

            $path = "invoices/{$filename}";
            Storage::disk('public')->put($path, $pdf->output());
            
            $downloadUrl = asset("storage/{$path}");
            
            return response()->json([
                'download_url' => $downloadUrl,
                'filename' => $filename,
                'generated_at' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while generating the PDF.'], 500);
        }
    }

    private function handleCreateUpdate(InvoiceRequest $request, InvoiceHeader $invoice)
    {
        try {

            DB::transaction(function() use($request,$invoice){
                $validated = $request->validated();
                $totalAmount = 0;
                $discountedTotal = 0;
                $items = $validated['items'] ?? [];
                foreach ($items as $item) {
                    $grossAmount = floatval($item['quantity']) * floatval($item['rate']);
                    $discountedTotal += floatval($item['discount'] ?? 0);
                    $totalAmount += $grossAmount;
                }

                $vatValues = calculateVAT(
                    $validated['vat'] ?? 0,
                    $totalAmount - $discountedTotal,
                    $validated['vat_type']
                );

                $dueDate = null;
                if (isset($validated['terms']) && is_numeric($validated['terms'])) {
                    $invoiceDate = Carbon::parse(date('Y-m-d', strtotime($validated['invoice_date'])));
                    $dueDate = $invoiceDate->addDays((int)$validated['terms'])->format('Y-m-d');
                }

                $invoice->fill(array_merge($validated, [
                    'invoice_date' => formatDate($validated['invoice_date']),
                    'due_date' => $dueDate,
                    'sub_total' => $totalAmount - $discountedTotal,
                    'discount' => $discountedTotal,
                    'vat_amount' => $vatValues['vat_amount'],
                    'total_amount' => $vatValues['inclusive_amount'],
                    'created_by' => $request->user()->id,
                ]));
                $invoice->save();

                $invoice->details()->delete();
                Ledger::where('transaction_type', 'invoice')
                        ->where('transaction_id', $invoice->id)
                        ->delete();

                foreach ($items as $item) {
                    $account = Service::find($item['service_id'])->account->name;
                    $discount = isset($item['discount']) ? floatval($item['discount']) : 0;
                    $amount = (floatval($item['quantity']) * floatval($item['rate'])) - $discount;
                    $itemVatValues = calculateVAT($validated['vat'] ?? 0,$amount,$validated['vat_type']);
                    $invoice->details()->create([
                        'service_id' => $item['service_id'],
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                        'discount' => $discount,
                        'amount' => $amount,
                    ]);

                    $this->addToLedgerMin($validated['invoice_date'],$account,0,
                            $itemVatValues['inclusive_amount'],null,$invoice->invoice_no,
                            'invoice',$invoice->id);
                }

                $this->addToLedgerMin($validated['invoice_date'],'accounts receivable',$vatValues['inclusive_amount'],
                            0,null,$invoice->invoice_no,
                            'invoice',$invoice->id);
            });

            return response()->json(['message' => 'Invoice processed successfully.'], $invoice->wasRecentlyCreated ? 201 : 200);
            
        } catch (Exception $e) {
            Log::error('Error processing invoice: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the invoice.'], 500);
        }
        // calculate discounted total
    }
}
