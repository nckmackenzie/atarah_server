<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
        }
        
        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .invoice-info {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 5px;
        }
        
        .company-details {
            color: #64748b;
            line-height: 1.6;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 5px;
        }
        
        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .bill-to, .ship-to {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .client-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3b82f6;
        }
        
        .client-name {
            /* font-size: 14px; */
            font-weight: medium;
            color: #1a365d;
            margin-bottom: 5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .items-table th {
            background-color: #1a365d;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals-section {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        
        .totals-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .totals-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .totals-table .total-row {
            background-color: #1a365d;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .payment-info {
            background-color: #f0f9ff;
            padding: 20px;
            border-radius: 6px;
            border-left: 4px solid #0ea5e9;
            margin-top: 20px;
        }
        
        .payment-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 10px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 11px;
        }
        
        .amount {
            font-weight: 600;
            color: #1a365d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-info">
                @if(file_exists($company['logo']))
                    <img src="{{ $company['logo'] }}" alt="Company Logo" class="company-logo">
                @endif
                <div class="company-name">Atarah Solutions</div>
                <div class="company-details">
                    P.O. Box 35211-00100, Nairobi, Kenya<br>
                    Phone: +254 721 442 223 | +254 734 442 223<br>
                    Email: grace@atarahsolutions.co.ke<br>
                    Website: https://atarahsolutions.co.ke<br/>
                    Tax Pin: P051802048C<br>
                </div>
            </div>
            
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">Invoice #: {{ $invoice->invoice_no }}</div>
                <div class="invoice-number">Date: {{ $invoice->invoice_date->format('M d, Y') }}</div>
                <div class="invoice-number">Due Date: {{ $invoice->due_date->format('M d, Y') }}</div>
                <div class="invoice-number">Due Date: {{ $balance }}</div>
                
                @php
                    $status = $balance <= 0 ? 'paid' : ($invoice->due_date->isPast() ? 'overdue' : 'pending');
                @endphp
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span>
                </div>
            </div>
        </div>

        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="client-info">
                    <div class="client-name">TO: THE MANAGER,</div>
                    <div class="client-name">{{ $invoice->client->name }}</div>
                </div>
            </div>            
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Service</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 15%;" class="text-right">Rate</th>
                    <th style="width: 10%;" class="text-right">Discount</th>
                    <th style="width: 15%;" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $detail)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $detail->service->name }}</div>
                        </td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-right amount">{{ number_format($detail->rate, 2) }}</td>
                        <td class="text-right">
                            @if($detail->discount > 0)
                                {{ number_format($detail->discount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right amount">{{ number_format($detail->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        <div class="totals-section">
            <div class="totals-left">
                @if($invoice->notes)
                    <div class="payment-info">
                        <div class="payment-title">Notes</div>
                        <div>{{ $invoice->notes }}</div>
                    </div>
                @endif
            </div>
            
            <div class="totals-right">
                <table class="totals-table">
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td class="text-right amount">Ksh {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discounted_total > 0)
                        <tr>
                            <td><strong>Total Discount:</strong></td>
                            <td class="text-right">-Ksh {{ number_format($invoice->discounted_total, 2) }}</td>
                        </tr>
                    @endif
                    @if($invoice->vat_amount > 0)
                        <tr>
                            <td><strong>VAT ({{ $invoice->vat }}%):</strong></td>
                            <td class="text-right amount">Ksh {{ number_format($vatAmount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td><strong>Total Amount:</strong></td>
                        <td class="text-right"><strong>Ksh {{ number_format($total, 2) }}</strong></td>
                    </tr>
                    @if($invoice->amount_paid > 0)
                        <tr>
                            <td><strong>Amount Paid:</strong></td>
                            <td class="text-right">-Ksh {{ number_format($invoice->amount_paid, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>Balance Due:</strong></td>
                            <td class="text-right"><strong>Ksh {{ number_format($balance, 2) }}</strong></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="payment-info">
            <div class="payment-title">Payment Information</div>
            <div>
                Make payments to: <strong>Atarah Solutions Limited</strong><br>
                Account Number: <strong>01192952546400</strong><br>
                Bank: <strong>Co-operative Bank</strong><br>
                Branch: <strong>Enterprise Road</strong><br>
            </div>
        </div>

        <div class="footer">
            <div>Thank you for your business!</div>
            <div style="margin-top: 5px;">
                If you have any questions regarding this invoice, please contact us at <em>grace@atarahsolutions.co.ke</em>
            </div>
        </div>
    </div>
</body>
</html>