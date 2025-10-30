<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura - {{ $invoice->invoice_number }}</title>
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
            padding: 15mm;
            background: white;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
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
            text-align: right;
        }
        
        .company-info img {
            max-height: 60px;
            max-width: 168px;
            margin-bottom: 10px;
            display: block;
        }
        
        .company-info p {
            margin-bottom: 3px;
            font-size: 12px;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .billed-to h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .billed-to table {
            font-size: 11px;
        }
        
        .billed-to td {
            padding: 2px 0;
        }
        
        .billed-to td:first-child {
            padding-right: 10px;
        }
        
        .invoice-summary table {
            font-size: 11px;
        }
        
        .invoice-summary td {
            padding: 2px 0;
        }
        
        .invoice-summary td:first-child {
            padding-right: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-size: 11px;
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
            margin-top: 30px;
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
            margin: 30px 0;
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
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="invoice-info">
                <h1>Fatura #{{ $invoice->invoice_number }}</h1>
                <p>Fatura Tarihi: {{ $invoice->invoice_date->format('d/m/Y') }}</p>
                <p>Vade Tarihi: {{ $invoice->due_date->format('d/m/Y') }}</p>
            </div>
            <div class="company-info">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Ronex Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none; font-size: 24px; font-weight: bold; color: #333; margin-bottom: 10px;">{{ config('company.name') }}</div>
                <p><strong>{{ config('company.full_name') }}</strong></p>
                <p>{{ config('company.address.street') }}</p>
                <p>{{ config('company.address.street2') }}</p>
                <p>{{ config('company.address.district') }}, {{ config('company.address.postal_code') }} {{ config('company.address.city') }}/{{ config('company.address.province') }}</p>
                <p>Tel: {{ config('company.contact.phone') }}</p>
                <p>E-mail: {{ config('company.contact.email') }}</p>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="billed-to">
                <h3>Fatura Edilen:</h3>
                <table>
                    <tr>
                        <td>Ad Soyad</td>
                        <td>: {{ $invoice->customer->name }}</td>
                    </tr>
                    @if($invoice->customer->company_name)
                    <tr>
                        <td>Şirket</td>
                        <td>: {{ $invoice->customer->company_name }}</td>
                    </tr>
                    @endif
                    @if($invoice->customer->address)
                    <tr>
                        <td>Adres</td>
                        <td>: {{ $invoice->customer->address }}</td>
                    </tr>
                    @endif
                    @if($invoice->customer->phone)
                    <tr>
                        <td>Telefon</td>
                        <td>: {{ $invoice->customer->phone }}</td>
                    </tr>
                    @endif
                    @if($invoice->customer->email)
                    <tr>
                        <td>E-posta</td>
                        <td>: {{ $invoice->customer->email }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="invoice-summary">
                <table>
                    <tr>
                        <td>Fatura Tarihi</td>
                        <td>: {{ $invoice->invoice_date->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td>Fatura Saati</td>
                        <td>: {{ $invoice->invoice_time }}</td>
                    </tr>
                    <tr>
                        <td>Vade Tarihi</td>
                        <td>: {{ $invoice->due_date->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td>Para Birimi</td>
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
                        <td>Durum</td>
                        <td>: 
                            @if($invoice->payment_completed)
                                Tahsilat Yapıldı
                            @else
                                @switch($invoice->status)
                                    @case('draft') Taslak @break
                                    @case('sent') Gönderildi @break
                                    @case('paid') Ödendi @break
                                    @case('overdue') Vadesi Geçti @break
                                    @case('cancelled') İptal @break
                                @endswitch
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center">Sıra</th>
                    <th>Ürün/Hizmet</th>
                    <th>Açıklama</th>
                    <th class="text-center">Miktar</th>
                    <th class="text-right">Birim Fiyat</th>
                    <th class="text-center">KDV %</th>
                    <th class="text-center">İndirim</th>
                    <th class="text-right">Toplam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_service_name }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
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
                    <td class="text-right">
                        {{ number_format($item->line_total, 2) }}
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
                        <td>Ara Toplam:</td>
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
                        <td>KDV:</td>
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
                        <td>Genel Toplam:</td>
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
            İşleminiz için teşekkür ederiz!
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">Müşteri İmzası</div>
            <div class="signature-box">Yetkili İmzası</div>
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
