<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Belanja</title>
    <style>
        @media print {
            @page {
                margin: 0;
                /* size: 58mm;  <-- Removing this to let printer driver handle "cut" if possible */
            }
            body {
                margin: 0;
            }
        }
        html, body {
            width: 58mm;
            height: max-content; /* Try to minimize height */
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
        }
        .container {
            width: 100%;
            padding: 2px 2px 20px 2px; /* Add bottom padding for content safety */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .dashed { border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; font-size: 10px; }
        
        .limit-text {
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            max-width: 30mm;
        }
    </style>
</head>
<body onload="window.print(); setTimeout(function(){ window.close(); }, 500);">
    <div class="container">
        <div class="text-center">
            <h4 style="margin: 0;">{{ env('APP_NAME', 'POS KERUPUK') }}</h4>
            <p style="margin: 0; font-size: 9px;">Jln. Raya Kerupuk No. 1</p>
        </div>
        
        <div class="dashed"></div>
        
        <table>
            <tr>
                <td>No: {{ $transaction->no_transaksi }}</td>
            </tr>
            <tr>
                <td>{{ date('d/m/Y H:i', strtotime($transaction->tanggal)) }}</td>
            </tr>
            <tr>
                <td>Kasir: {{ $transaction->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Plg: {{ $transaction->customer->nama ?? 'Umum' }}</td>
            </tr>
        </table>
        
        <div class="dashed"></div>
        
        <table>
            @foreach($transaction->details as $detail)
            <tr>
                <td colspan="3" style="padding-top: 3px;">{{ $detail->product->nama_produk }}</td>
            </tr>
            <tr>
                <td>{{ $detail->jumlah + 0 }}x</td>
                <td class="text-right">{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
        
        <div class="dashed"></div>
        
        <table>
            <tr>
                <td>Total</td>
                <td class="text-right bold">{{ number_format($transaction->total_harga, 0, ',', '.') }}</td>
            </tr>
            @if($transaction->biaya_kirim > 0)
            <tr>
                <td>Ongkir</td>
                <td class="text-right">{{ number_format($transaction->biaya_kirim, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($transaction->biaya_tambahan > 0)
            <tr>
                <td>Lainnya</td>
                <td class="text-right">{{ number_format($transaction->biaya_tambahan, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="bold">Grand Total</td>
                <td class="text-right bold">{{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bayar</td>
                <td class="text-right">{{ number_format($transaction->bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembali</td>
                <td class="text-right">{{ number_format($transaction->kembalian, 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div class="dashed"></div>
        
        <div class="text-center">
            <p style="margin: 5px 0;">Terima Kasih</p>
        </div>
    </div>
</body>
</html>
