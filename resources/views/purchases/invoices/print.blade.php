<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alış Faturası - {{ $invoice->invoice_number }}</title>
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
        }
        
        .company-info h2 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info p {
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .supplier-info, .invoice-meta {
            flex: 1;
        }
        
        .supplier-info h3, .invoice-meta h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .supplier-info p, .invoice-meta p {
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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
            text-align: center;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        
        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }
        
        .totals-table td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 11px;
        }
        
        .totals-table .label {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: right;
        }
        
        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }
        
        .totals-table .total-row {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 13px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .invoice-container {
                margin: 0;
                padding: 10mm;
                max-width: none;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="invoice-info">
                <h1>ALIŞ FATURASI</h1>
                <p><strong>Fatura No:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Fatura Tarihi:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
                <p><strong>Vade Tarihi:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
            </div>
            <div class="company-info">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Ronex Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none; font-size: 20px; font-weight: bold; margin-bottom: 10px;">RONEX</div>
                <h2>Ronex Tekstil San. Tic. Ltd. Şti.</h2>
                <p>Örnek Mahallesi, Tekstil Caddesi No:123</p>
                <p>İstanbul, Türkiye</p>
                <p>Tel: +90 212 123 45 67</p>
                <p>E-posta: info@ronex.com</p>
            </div>
        </div>

        <!-- Details -->
        <div class="invoice-details">
            <div class="supplier-info">
                <h3>Tedarikçi Bilgileri</h3>
                <p><strong>{{ $invoice->supplier->name ?? 'Tedarikçi Silinmiş' }}</strong></p>
                @if($invoice->supplier && $invoice->supplier->company_name)
                    <p>{{ $invoice->supplier->company_name }}</p>
                @endif
                @if($invoice->supplier && $invoice->supplier->address)
                    <p>{{ $invoice->supplier->address }}</p>
                @endif
                @if($invoice->supplier && $invoice->supplier->phone)
                    <p>Tel: {{ $invoice->supplier->phone }}</p>
                @endif
                @if($invoice->supplier && $invoice->supplier->email)
                    <p>E-posta: {{ $invoice->supplier->email }}</p>
                @endif
            </div>
            <div class="invoice-meta">
                <h3>Fatura Bilgileri</h3>
                <p><strong>Para Birimi:</strong> {{ $invoice->currency }}</p>
                <p><strong>KDV Durumu:</strong> {{ $invoice->vat_status === 'included' ? 'KDV Dahil' : 'KDV Hariç' }}</p>
                <p><strong>Ödeme Durumu:</strong> 
                    @if($invoice->payment_completed)
                        <span class="status-badge status-paid">Ödendi</span>
                            @else
                        <span class="status-badge status-pending">Beklemede</span>
                    @endif
                </p>
            </div>
        </div>
        
        @if($invoice->description)
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px;">Açıklama</h3>
            <p style="font-size: 11px;">{{ $invoice->description }}</p>
        </div>
        @endif

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Sıra</th>
                    <th style="width: 30%;">Ürün/Hizmet</th>
                    <th style="width: 20%;">Açıklama</th>
                    <th style="width: 8%;">Miktar</th>
                    <th style="width: 10%;">Birim Fiyat</th>
                    <th style="width: 8%;">KDV %</th>
                    <th style="width: 8%;">İndirim %</th>
                    <th style="width: 11%;">Toplam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_service_name }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }} {{ $item->unit_currency }}</td>
                    <td class="text-center">{{ number_format($item->tax_rate, 1) }}%</td>
                    <td class="text-center">{{ number_format($item->discount_rate, 1) }}%</td>
                    <td class="text-right">{{ number_format($item->line_total, 2) }} {{ $item->unit_currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td class="label">Ara Toplam:</td>
                    <td class="amount">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="label">İndirim:</td>
                    <td class="amount">-{{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endif
                @if($invoice->additional_discount > 0)
                <tr>
                    <td class="label">Ek İndirim:</td>
                    <td class="amount">-{{ number_format($invoice->additional_discount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                            @endif
                @if($invoice->vat_amount > 0)
                <tr>
                    <td class="label">KDV:</td>
                    <td class="amount">{{ number_format($invoice->vat_amount, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                    @endif
                <tr class="total-row">
                    <td class="label">GENEL TOPLAM:</td>
                    <td class="amount">{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                </tr>
                </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Bu alış faturası elektronik ortamda oluşturulmuştur.</p>
            <p>Ronex Tekstil San. Tic. Ltd. Şti. - {{ date('Y') }}</p>
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