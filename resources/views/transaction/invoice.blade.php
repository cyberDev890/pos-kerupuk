<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $transaction->no_transaksi }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .company-details h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .company-details p {
            margin: 5px 0;
            color: #555;
        }
        .invoice-meta {
            text-align: right;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }
        .meta-table {
            float: right;
        }
        .meta-table td {
            text-align: right;
            padding: 2px 10px;
        }
        .meta-table td:first-child {
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
        }
        
        .customer-section {
            margin-bottom: 30px;
            clear: both;
            padding-top: 20px;
        }
        .customer-section h3 {
            margin: 0 0 10px 0;
            text-transform: uppercase;
            font-size: 12px;
            color: #777;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            width: 200px;
        }
        .customer-details {
            font-size: 16px;
            font-weight: bold;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.items th {
            background: #f8f9fa;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        table.items td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        table.items .total-col {
            text-align: right;
        }
        table.items .qty-col {
            text-align: center;
        }
        
        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .payment-info {
            width: 45%;
            font-size: 12px;
            color: #555;
        }
        .totals {
            width: 50%;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .totals-table td {
            padding: 5px 0;
            text-align: right;
        }
        .totals-table td:first-child {
            color: #555;
            padding-right: 20px;
            font-weight: bold;
        }
        .totals-table .grand-total td {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 5px;
            color: #000;
        }

        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 200px;
            margin-top: 10px;
        }
        .signature-line {
            margin-top: 80px;
            border-top: 1px solid #333;
        }
        .thank-you {
            margin-top: 50px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 2px;
            color: #777;
        }

        @media print {
            body { 
                padding: 0; 
                -webkit-print-color-adjust: exact;
            }
            .invoice-box {
                box-shadow: none;
                border: none;
                max-width: 100%;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="invoice-box">
        <div class="header">
            <div class="company-details">
                <h1>JAYA ABADI</h1>
                <p>Jl. Ijen Dukusia Rambipuji</p>
                <p>082330634269</p>
            </div>
            <div class="invoice-meta">
                <h1 class="invoice-title">INVOICE</h1>
                <table class="meta-table">
                    <tr>
                        <td>Kepada:</td>
                        <td style="text-align: right;"><strong>{{ $transaction->customer->nama ?? 'Umum' }}</strong></td>
                    </tr>
                    <tr>
                        <td>No HP:</td>
                        <td style="text-align: right;">{{ $transaction->customer->no_hp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal:</td>
                        <td style="text-align: right;">{{ date('d F Y', strtotime($transaction->tanggal)) }}</td>
                    </tr>
                    <tr>
                        <td>No Invoice:</td>
                        <td style="text-align: right;">#{{ $transaction->no_transaksi }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 40%">Keterangan</th>
                    <th class="qty-col">Satuan</th>
                    <th>Harga</th>
                    <th class="qty-col">Jml</th>
                    <th class="total-col">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                <tr>
                    <td>
                        {{ $detail->product->nama_produk }}
                        @if($detail->unit_info)
                            <div style="font-size: 11px; color: #777; margin-top: 2px;">{{ $detail->unit_info }}</div>
                        @endif
                    </td>
                    <td class="qty-col">{{ $detail->unit->satuan_kecil ?? 'Pcs' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                    <td class="qty-col">{{ (float)$detail->jumlah }}</td>
                    <td class="total-col">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <div class="payment-info">
                <strong>STATUS PEMBAYARAN:</strong><br>
                @if($transaction->remaining_debt > 0) 
                    <span style="color: red; font-weight: bold; font-size: 16px;">BELUM LUNAS (HUTANG)</span>
                @else
                    <span style="color: green; font-weight: bold; font-size: 16px;">LUNAS</span>
                @endif
                <br><br>
                <small>Kasir: {{ $transaction->user->name ?? '-' }}</small>
            </div>
            <div class="totals">
                <table class="totals-table">
                    <tr>
                        <td>SUB TOTAL</td>
                        <td>Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}</td>
                    </tr>
                    @if($transaction->biaya_kirim > 0)
                    <tr>
                        <td>BIAYA KIRIM</td>
                        <td>Rp {{ number_format($transaction->biaya_kirim, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($transaction->biaya_tambahan > 0)
                    <tr>
                        <td>BIAYA TAMBAHAN</td>
                        <td>Rp {{ number_format($transaction->biaya_tambahan, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td>TOTAL</td>
                        <td>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>BAYAR</td>
                        <td>Rp {{ number_format($transaction->bayar, 0, ',', '.') }}</td>
                    </tr>
                    @if($transaction->remaining_debt > 0)
                    <tr>
                        <td style="color: red;">SISA HUTANG</td>
                        <td style="color: red; font-weight: bold;">Rp {{ number_format($transaction->remaining_debt, 0, ',', '.') }}</td>
                    </tr>
                    @else
                    <tr>
                        <td>KEMBALI</td>
                        <td>Rp {{ number_format($transaction->kembalian, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="footer">
            <div class="signature-box">
                <p>Penerima</p>
                <div class="signature-line"></div>
                <strong>{{ $transaction->customer->nama ?? 'Pelanggan' }}</strong>
            </div>
            <div class="signature-box">
                <p>Hormat Kami</p>
                <div class="signature-line"></div>
                <strong>JAYA ABADI</strong>
            </div>
        </div>

        <div class="thank-you">
            TERIMA KASIH ATAS KUNJUNGAN ANDA
        </div>
    </div>

</body>
</html>
