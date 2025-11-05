<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tedarikçi Ödeme Makbuzu - {{ $supplierPayment->id }}</title>
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
            color: #dc2626;
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
            <h1>Tedarikçi Ödeme Makbuzu</h1>
            <div class="company-name">RONEX TEKSTİL</div>
            <div class="company-info">
                Finansal Yönetim Sistemi<br>
               www.ronextekstil.com
            </div>
        </div>
        
        <!-- Receipt Number -->
        <div class="receipt-number">
            Makbuz No: <strong>ÖDE-{{ str_pad($supplierPayment->id, 6, '0', STR_PAD_LEFT) }}</strong>
        </div>
        
        <!-- Receipt Details -->
        <div class="receipt-details">
            <table class="receipt-table">
                <tr>
                    <td class="label">Ödeme Tarihi:</td>
                    <td class="value">{{ $supplierPayment->transaction_date->format('d.m.Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Tedarikçi Adı:</td>
                    <td class="value">{{ $supplierPayment->supplier->name ?? 'Tedarikçi Silinmiş' }}</td>
                </tr>
                @if($supplierPayment->supplier && $supplierPayment->supplier->company_name)
                <tr>
                    <td class="label">Firma Adı:</td>
                    <td class="value">{{ $supplierPayment->supplier->company_name }}</td>
                </tr>
                @endif
                @if($supplierPayment->supplier && $supplierPayment->supplier->tax_number)
                <tr>
                    <td class="label">Vergi No:</td>
                    <td class="value">{{ $supplierPayment->supplier->tax_number }}</td>
                </tr>
                @endif
                @if($supplierPayment->supplier && $supplierPayment->supplier->address)
                <tr>
                    <td class="label">Adres:</td>
                    <td class="value">{{ $supplierPayment->supplier->address }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Ödeme Türü:</td>
                    <td class="value">{{ $supplierPayment->payment_type_text }}</td>
                </tr>
                <tr>
                    <td class="label">Para Birimi:</td>
                    <td class="value">{{ $supplierPayment->currency }}</td>
                </tr>
                @if($supplierPayment->description)
                <tr>
                    <td class="label">Açıklama:</td>
                    <td class="value">{{ $supplierPayment->description }}</td>
                </tr>
                @endif
            </table>
        </div>
        
        <!-- Amount Section -->
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">Ödenen Tutar:</span>
                <span class="amount-value">{{ number_format($supplierPayment->amount, 2) }} {{ $supplierPayment->currency }}</span>
            </div>
            <div class="amount-words">
                <strong>Yazıyla:</strong> {{ $amountInWords }}
            </div>
            @if(isset($remainingBalances))
            <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #d1d5db;">
                <div class="amount-label" style="margin-bottom:8px;">Kalan Bakiye (Tüm Para Birimleri):</div>
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
                <div class="signature-line">Ödeme Yapan</div>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    Tarih: {{ \Carbon\Carbon::now()->format('d.m.Y') }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Ödeme Alan</div>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    {{ $supplierPayment->supplier->name ?? 'Tedarikçi' }}
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            Bu makbuz {{ \Carbon\Carbon::now()->format('d.m.Y H:i') }} tarihinde sistem tarafından otomatik olarak oluşturulmuştur.<br>
            Makbuz ID: {{ $supplierPayment->id }} | Oluşturulma: {{ $supplierPayment->created_at->format('d.m.Y H:i') }}
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
