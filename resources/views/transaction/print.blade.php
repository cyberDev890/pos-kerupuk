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
                size: 58mm auto; /* Experimental: Try to force auto height */
            }
            body {
                margin: 0;
                padding-bottom: 20px; /* Give space for cutter */
            }
            /* Hide Browser Header/Footer */
            header, footer { display: none !important; }
        }
        html, body {
            width: 58mm;
            /* height: max-content; Removed to prevent conflict with @page */
            margin: 0;
            padding: 0;
            font-family: 'Consolas', 'Lucida Console', 'Courier New', monospace; /* Lebih tebal */
            font-size: 12px; /* Diperbesar dari 10px */
            color: #000;
            font-weight: 600; /* Agak bold biar jelas */
        }
        .container {
            width: 100%;
            padding: 0 2px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: 800; font-size: 13px; }
        .dashed { border-top: 2px dashed #000; margin: 8px 0; } /* Garis lebih tebal */
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; font-size: 12px; padding-bottom: 2px; }
        
        /* Judul Toko Besar */
        h4 { font-size: 16px; font-weight: 900; margin-bottom: 5px; }
        
        .truncate {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 100px; /* Sesuai saran teman (sekitar 7-8em) */
            display: inline-block;
            vertical-align: middle;
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
            <tr>
                <td colspan="3" style="padding-top: 3px;" class="truncate">{{ $detail->product->nama_produk }}</td>
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
