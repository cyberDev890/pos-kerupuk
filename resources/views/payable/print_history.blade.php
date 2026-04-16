<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Hutang - {{ $purchase->no_faktur }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { padding: 5px; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
        @media print {
            .no-print { display: none; }
            body { font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print()">Cetak</button>
            <button onclick="window.close()">Tutup</button>
        </div>

        <div class="header">
            <h2>KARTU HUTANG</h2>
            <p>Demo APP - Jl. Dummy No. 123, Kota Dummy</p>
        </div>

        <table class="meta-table">
            <tr>
                <td width="15%"><strong>Suplier</strong></td>
                <td width="2%">:</td>
                <td width="33%">{{ $purchase->supplier->nama ?? 'Umum' }}</td>
                <td width="15%"><strong>No Faktur</strong></td>
                <td width="2%">:</td>
                <td width="33%">{{ $purchase->no_faktur }}</td>
            </tr>
            <tr>
                <td><strong>Alamat</strong></td>
                <td>:</td>
                <td>{{ $purchase->supplier->alamat ?? '-' }}</td>
                <td><strong>Tanggal Awal</strong></td>
                <td>:</td>
                <td>{{ date('d/m/Y H:i', strtotime($purchase->tanggal)) }}</td>
            </tr>
        </table>

        <!-- Item Details (Optional Summary) -->
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->details as $detail)
                <tr>
                    <td>{{ $detail->product->nama_produk ?? $detail->nama_produk }}</td>
                    <td class="text-center">{{ (float)$detail->jumlah }}</td>
                    <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                 <tr>
                    <td colspan="3" class="text-right"><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($purchase->total_harga, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <h3>Riwayat Pembayaran</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal & Jam</th>
                    <th>Petugas</th>
                    <th>Catatan</th>
                    <th class="text-right">Jumlah Bayar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->payments as $index => $pay)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ date('d/m/Y H:i', strtotime($pay->payment_date)) }}</td>
                    <td>{{ $pay->user->name ?? '-' }}</td>
                    <td>{{ $pay->note }}</td>
                    <td class="text-right">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>Total Terbayar</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($purchase->bayar, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-right" style="color: red;"><strong>Sisa Hutang</strong></td>
                    <td class="text-right" style="color: red;"><strong>Rp {{ number_format($purchase->remaining_debt, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>

         <div class="footer">
            <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
