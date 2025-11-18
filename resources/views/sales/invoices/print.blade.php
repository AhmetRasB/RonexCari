<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $translations['invoice'] }} - {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .invoice-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 2px solid #333;
        }
        
        .invoice-info h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .invoice-info p {
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .company-info {
            text-align: left;
        }
        .logo-small img { height: 40px; width: auto; display: block; }
        .company-line { font-size: 9px; line-height: 1.3; }
        .company-sep { padding: 0 6px; color: #999; }
        
        .company-inline {
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            margin-bottom: 6px;
        }
        .meta-left, .meta-right {
            display: flex;
            gap: 10px;
            flex-wrap: nowrap;
            white-space: nowrap;
        }
        .meta-item {
            white-space: nowrap;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: left;
            font-size: 9.5px;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .invoice-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 10px;
        }
        
        .notes {
            flex: 1;
        }
        
        .notes p {
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .totals {
            text-align: right;
        }
        
        .totals table {
            font-size: 12px;
        }
        
        .totals td {
            padding: 3px 0;
        }
        
        .totals td:first-child {
            padding-right: 20px;
        }
        
        .totals .total-row {
            border-top: 2px solid #333;
            font-weight: bold;
            padding-top: 5px;
        }
        
        .totals .tl-row {
            color: #666;
            font-size: 11px;
        }
        
        .thank-you {
            text-align: center;
            margin: 12px 0;
            font-weight: bold;
            font-size: 12px;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-box {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 11px;
            text-align: center;
            width: 150px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 10mm;
            }
            
            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        @php
            $itemsCount = $invoice->items->count();
        @endphp
        <!-- Compact Header (one-row) -->
        <div class="invoice-header">
            <div class="company-info" style="display:flex; align-items:center; gap:10px; width: 80%;">
                <div class="logo-small">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
                </div>
                <div>
                    <div class="company-inline">{{ config('company.full_name') }}</div>
                    <div class="company-line">
                        {{ trim(config('company.address.street') . ' ' . (config('company.address.street2') ?? '')) }}
                        <span class="company-sep">•</span>
                        {{ config('company.address.district') }}, {{ config('company.address.postal_code') }} {{ config('company.address.city') }}/{{ config('company.address.province') }}
                        <span class="company-sep">•</span>
                        Tel: {{ config('company.contact.phone') }}
                        <span class="company-sep">•</span>
                        {{ config('company.contact.email') }}
                    </div>
                </div>
            </div>
            <div class="meta-right" style="font-size:11px; text-align:right; width: 20%;">
                <span class="meta-item">{{ $translations['invoice'] }} #{{ $invoice->invoice_number }}</span>
            </div>
        </div>

        <!-- Compact meta bar (customer + order info in one line) -->
        @if($itemsCount > 10)
        <div class="meta-bar">
            <div class="meta-left">
                <span class="meta-item"><strong>{{ $translations['billed_to'] }}:</strong> {{ $invoice->customer?->name ?? '-' }}</span>
                    @if($invoice->customer && $invoice->customer->company_name)
                <span class="meta-item">| {{ $invoice->customer->company_name }}</span>
                    @endif
            </div>
            <div class="meta-right">
                <span class="meta-item">{{ $translations['invoice_date'] }}: {{ $invoice->invoice_date->format('d.m.Y') }}</span>
                <span class="meta-item">| {{ $translations['time'] }}: {{ $invoice->invoice_time }}</span>
                <span class="meta-item">| {{ $translations['due_date'] }}: {{ $invoice->due_date->format('d.m.Y') }}</span>
                <span class="meta-item">| {{ $translations['currency'] }}:
                    @if($invoice->currency === 'USD') USD
                    @elseif($invoice->currency === 'EUR') EUR
                    @else TRY @endif
                </span>
            </div>
        </div>
        <!-- Mini customer details under meta for compact layout -->
        <div style="font-size:9px; margin: 4px 0 6px 0;">
            <span class="meta-item"><strong>{{ $translations['name'] }}:</strong> {{ $invoice->customer?->name ?? '-' }}</span>
            @if($invoice->customer && $invoice->customer->company_name)
                <span class="company-sep">•</span><span class="meta-item"><strong>{{ $translations['company'] }}:</strong> {{ $invoice->customer->company_name }}</span>
            @endif
            @if($invoice->customer && $invoice->customer->phone)
                <span class="company-sep">•</span><span class="meta-item"><strong>{{ $translations['phone'] }}:</strong> {{ $invoice->customer->phone }}</span>
            @endif
            @if($invoice->customer && $invoice->customer->email)
                <span class="company-sep">•</span><span class="meta-item"><strong>{{ $translations['email'] }}:</strong> {{ $invoice->customer->email }}</span>
            @endif
            @if($invoice->customer && $invoice->customer->address)
                <span class="company-sep">•</span><span class="meta-item"><strong>{{ $translations['address'] }}:</strong> {{ $invoice->customer->address }}</span>
            @endif
        </div>
        @else
        <!-- Original detailed layout when few items -->
        <div class="invoice-details" style="margin-bottom: 12px;">
            <div class="billed-to">
                <h3 style="margin-bottom:6px;">{{ $translations['billed_to'] }}:</h3>
                <table>
                    <tr>
                        <td>{{ $translations['name'] }}</td>
                        <td>: {{ $invoice->customer?->name ?? '-' }}</td>
                    </tr>
                    @if($invoice->customer && $invoice->customer->company_name)
                    <tr>
                        <td>{{ $translations['company'] }}</td>
                        <td>: {{ $invoice->customer->company_name }}</td>
                    </tr>
                    @endif
                    @if($invoice->customer && $invoice->customer->address)
                    <tr>
                        <td>{{ $translations['address'] }}</td>
                        <td>: {{ $invoice->customer->address }}</td>
                    </tr>
                    @endif
                    @if($invoice->customer && $invoice->customer->phone)
                    <tr>
                        <td>{{ $translations['phone'] }}</td>
                        <td>: {{ $invoice->customer->phone }}</td>
                    </tr>
                    @endif
                    @if($invoice->customer && $invoice->customer->email)
                    <tr>
                        <td>{{ $translations['email'] }}</td>
                        <td>: {{ $invoice->customer->email }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="invoice-summary">
                <table>
                    <tr>
                        <td>{{ $translations['invoice_date'] }}</td>
                        <td>: {{ $invoice->invoice_date->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td>{{ $translations['time'] }}</td>
                        <td>: {{ $invoice->invoice_time }}</td>
                    </tr>
                    <tr>
                        <td>{{ $translations['due_date'] }}</td>
                        <td>: {{ $invoice->due_date->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td>{{ $translations['currency'] }}</td>
                        <td>: 
                            @if($invoice->currency === 'USD')
                                $ USD
                            @elseif($invoice->currency === 'EUR')
                                € EUR
                            @else
                                ₺ TRY
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>{{ $translations['status'] }}</td>
                        <td>: 
                            @if($invoice->payment_completed)
                                {{ $translations['paid'] }}
                            @else
                                @switch($invoice->status)
                                    @case('draft') {{ $translations['draft'] }} @break
                                    @case('sent') {{ $translations['sent'] }} @break
                                    @case('paid') {{ $translations['paid_status'] }} @break
                                    @case('overdue') {{ $translations['overdue'] }} @break
                                    @case('cancelled') {{ $translations['cancelled'] }} @break
                                @endswitch
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center">{{ $translations['no'] }}</th>
                    <th>{{ $translations['product_service'] }}</th>
                    <th>{{ $translations['description'] }}</th>
                    <th class="text-center">{{ $translations['quantity'] }}</th>
                    <th class="text-right">{{ $translations['unit_price'] }}</th>
                    <th class="text-center">{{ $translations['vat'] }}</th>
                    <th class="text-center">{{ $translations['discount'] }}</th>
                    <th class="text-right">{{ $translations['total'] }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($invoice->items as $index => $item)
            @php
                $isExchange = str_starts_with($item->description ?? '', 'Değişim -');
            @endphp
            <tr class="{{ $item->is_return ? 'table-danger' : ($isExchange ? 'table-info' : '') }}" style="{{ $item->is_return ? 'background-color: #fee; border-left: 3px solid #dc2626;' : ($isExchange ? 'background-color: #e6f3ff; border-left: 3px solid #0dcaf0;' : '') }}">
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    {{ $item->product_service_name }}
                    @if($item->is_return)
                        <span class="badge bg-danger ms-2" style="font-size: 10px;">İADE</span>
                    @elseif($isExchange)
                        <span class="badge bg-info ms-2" style="font-size: 10px;">DEĞİŞİM</span>
                    @endif
                    @if($item->selected_color)
                        <br><small class="text-muted">Renk: {{ $item->selected_color }}</small>
                    @endif
                </td>
                <td>{{ $item->description ?? ($item->is_return ? 'İade' : ($isExchange ? 'Değişim' : '-')) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">
                        {{ number_format($item->unit_price, 2) }}
                        @if($invoice->currency === 'USD')
                            $
                        @elseif($invoice->currency === 'EUR')
                            €
                        @else
                            ₺
                        @endif
                    </td>
                    <td class="text-center">%{{ $item->tax_rate }}</td>
                    <td class="text-center">
                        {{ number_format($item->discount_rate, 2) }}
                        @if($invoice->currency === 'USD')
                            $
                        @elseif($invoice->currency === 'EUR')
                            €
                        @else
                            ₺
                        @endif
                    </td>
                    <td class="text-right {{ $item->is_return ? 'text-danger fw-bold' : '' }}">
                        @if($item->is_return)
                            -{{ number_format(abs($item->line_total), 2) }}
                        @else
                        {{ number_format($item->line_total, 2) }}
                        @endif
                        @if($invoice->currency === 'USD')
                            $
                        @elseif($invoice->currency === 'EUR')
                            €
                        @else
                            ₺
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Invoice Footer -->
        <div class="invoice-footer">
            <div class="notes">
                @if($invoice->description)
                <p><strong>Açıklama:</strong> {{ $invoice->description }}</p>
                @endif
                <p>İşlem tarihi: {{ $invoice->created_at->format('d.m.Y H:i') }}</p>
            </div>
            <div class="totals">
                <table>
                    <tr>
                        <td>{{ $translations['subtotal'] }}:</td>
                        <td>
                            {{ number_format($invoice->subtotal, 2) }}
                            @if($invoice->currency === 'USD')
                                $
                            @elseif($invoice->currency === 'EUR')
                                €
                            @else
                                ₺
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>{{ $translations['vat_amount'] }}:</td>
                        <td>
                            {{ number_format($invoice->vat_amount, 2) }}
                            @if($invoice->currency === 'USD')
                                $
                            @elseif($invoice->currency === 'EUR')
                                €
                            @else
                                ₺
                            @endif
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td>{{ $translations['grand_total'] }}:</td>
                        <td>
                            {{ number_format($invoice->total_amount, 2) }}
                            @if($invoice->currency === 'USD')
                                $
                            @elseif($invoice->currency === 'EUR')
                                €
                            @else
                                ₺
                            @endif
                        </td>
                    </tr>
                    
                </table>
            </div>
        </div>

        <!-- Thank You Message -->
        <div class="thank-you">
            {{ $translations['thank_you'] }}
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">{{ $translations['customer_signature'] }}</div>
            <div class="signature-box">{{ $translations['authorized_signature'] }}</div>
        </div>
    </div>

    

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
