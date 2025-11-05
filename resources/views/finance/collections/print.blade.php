<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tahsilat Makbuzu - {{ $collection->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .receipt-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
        }
        
        /* Mobile responsive for print */
        @media screen and (max-width: 768px) {
            .receipt-container {
                padding: 10mm;
                max-width: 100%;
            }
            
            .receipt-header h1 {
                font-size: 24px;
            }
            
            .receipt-header .company-name {
                font-size: 16px;
            }
            
            .receipt-table th,
            .receipt-table td {
                padding: 8px;
                font-size: 12px;
            }
            
            .amount-value {
                font-size: 18px;
            }
            
            .amount-words {
                font-size: 12px;
            }
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px double #333;
        }
        
        .receipt-header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .receipt-header .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2563eb;
        }
        
        .receipt-header .company-info {
            font-size: 12px;
            color: #666;
        }
        
        .receipt-number {
            text-align: right;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .receipt-details {
            margin-bottom: 30px;
        }
        
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .receipt-table th,
        .receipt-table td {
            border: 2px solid #333;
            padding: 12px;
            text-align: left;
        }
        
        .receipt-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 14px;
        }
        
        .receipt-table .label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 25%;
        }
        
        .receipt-table .value {
            width: 75%;
        }
        
        .amount-section {
            margin: 30px 0;
            padding: 20px;
            border: 3px double #333;
            background-color: #f8f9fa;
        }
        
        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .amount-label {
            font-weight: bold;
            font-size: 16px;
        }
        
        .amount-value {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
        }
        
        .amount-words {
            font-style: italic;
            text-transform: capitalize;
            font-size: 14px;
            color: #374151;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 10px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        @media print {
            body {
                margin: 0;
                font-size: 12px;
            }
            
            .receipt-container {
                padding: 10mm;
                box-shadow: none;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                margin: 0;
                size: A4;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Yazdır</button>
    
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1>{{ $translations['receipt'] ?? 'Tahsilat Makbuzu' }}</h1>
            <div class="company-name">RONEX TEKSTİL</div>
            <div class="company-info">
                Finansal Yönetim Sistemi<br>
               www.ronextekstil.com
            </div>
        </div>
        
        <!-- Receipt Number -->
        <div class="receipt-number">
            {{ $translations['receipt_no'] ?? 'Makbuz No' }}: <strong>TAH-{{ str_pad($collection->id, 6, '0', STR_PAD_LEFT) }}</strong>
        </div>
        
        <!-- Receipt Details -->
        <div class="receipt-details">
            <table class="receipt-table">
                <tr>
                    <td class="label">{{ $translations['date'] ?? 'Tarih' }}:</td>
                    <td class="value">{{ $collection->transaction_date->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <td class="label">{{ $translations['customer'] ?? 'Müşteri' }}:</td>
                    <td class="value">{{ $collection->customer->name ?? 'Müşteri Silinmiş' }}</td>
                </tr>
                @if($collection->customer && $collection->customer->company_name)
                <tr>
                    <td class="label">{{ $translations['company'] ?? 'Şirket' }}:</td>
                    <td class="value">{{ $collection->customer->company_name }}</td>
                </tr>
                @endif
                @if($collection->customer && $collection->customer->tax_number)
                <tr>
                    <td class="label">{{ $translations['tax_no'] ?? 'Vergi No' }}:</td>
                    <td class="value">{{ $collection->customer->tax_number }}</td>
                </tr>
                @endif
                @if($collection->customer && $collection->customer->address)
                <tr>
                    <td class="label">{{ $translations['address'] ?? 'Adres' }}:</td>
                    <td class="value">{{ $collection->customer->address }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">{{ $translations['collection_type'] ?? 'Tahsilat Türü' }}:</td>
                    <td class="value">{{ $collection->collection_type_text }}</td>
                </tr>
                <tr>
                    <td class="label">{{ $translations['currency'] ?? 'Para Birimi' }}:</td>
                    <td class="value">{{ $collection->currency }}</td>
                </tr>
                @if($collection->description)
                <tr>
                    <td class="label">{{ $translations['description'] ?? 'Açıklama' }}:</td>
                    <td class="value">{{ $collection->description }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <!-- Amount Section -->
        <div class="amount-section">
            @if($collection->discount > 0)
            <div class="amount-row" style="margin-bottom: 12px;">
                <span class="amount-label">Total Debt:</span>
                <span class="amount-value" style="color:#374151;">{{ number_format($collection->amount + $collection->discount, 2) }} {{ $collection->currency }}</span>
            </div>
            <div class="amount-row" style="margin-bottom: 12px;">
                <span class="amount-label">Discount:</span>
                <span class="amount-value" style="color:#dc2626; font-weight: bold;">-{{ number_format($collection->discount, 2) }} {{ $collection->currency }}</span>
            </div>
            <div style="border-top: 2px solid #333; margin: 15px 0; padding-top: 12px;">
                <div class="amount-row">
                    <span class="amount-label">{{ $translations['collected_amount'] ?? 'Tahsil Edilen Tutar' }}:</span>
                    <span class="amount-value" style="color:#059669; font-weight: bold;">{{ number_format($collection->amount, 2) }} {{ $collection->currency }}</span>
                </div>
            </div>
            @else
            <div class="amount-row">
                <span class="amount-label">{{ $translations['collected_amount'] ?? 'Tahsil Edilen Tutar' }}:</span>
                <span class="amount-value">{{ number_format($collection->amount, 2) }} {{ $collection->currency }}</span>
            </div>
            @endif
            <div class="amount-words">
                <strong>{{ $translations['in_words'] ?? 'Yazıyla' }}:</strong> {{ $amountInWords }}
            </div>
            @if(isset($remainingBalances))
            <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #d1d5db;">
                <div class="amount-label" style="margin-bottom:8px;">{{ $translations['remaining_all'] ?? 'Kalan Bakiye (Tüm Para Birimleri)' }}:</div>
                <table style="width:100%; font-size:13px;">
                    <tr>
                        <td style="width:33%;"><strong>₺ TRY:</strong></td>
                        <td class="amount-value" style="text-align:right; color:#dc2626;">{{ number_format($remainingBalances['TRY'] ?? 0, 2) }} ₺</td>
                    </tr>
                    <tr>
                        <td><strong>$ USD:</strong></td>
                        <td class="amount-value" style="text-align:right; color:#dc2626;">{{ number_format($remainingBalances['USD'] ?? 0, 2) }} $</td>
                    </tr>
                    <tr>
                        <td><strong>€ EUR:</strong></td>
                        <td class="amount-value" style="text-align:right; color:#dc2626;">{{ number_format($remainingBalances['EUR'] ?? 0, 2) }} €</td>
                    </tr>
                </table>
            </div>
            @endif
        </div>
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">{{ $translations['collector'] ?? 'Tahsil Eden' }}</div>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    Tarih: {{ \Carbon\Carbon::now()->format('d.m.Y') }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">{{ $translations['collected_from'] ?? 'Tahsil Edilen' }}</div>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    {{ $collection->customer->name ?? 'Müşteri' }}
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            Bu makbuz {{ \Carbon\Carbon::now()->format('d.m.Y H:i') }} tarihinde sistem tarafından otomatik olarak oluşturulmuştur.<br>
            Makbuz ID: {{ $collection->id }} | Oluşturulma: {{ $collection->created_at->format('d.m.Y H:i') }}
        </div>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
        
        // Close window after printing
        window.onafterprint = function() {
            // window.close(); // Uncomment if you want to close after print
        }
    </script>
</body>
</html>
