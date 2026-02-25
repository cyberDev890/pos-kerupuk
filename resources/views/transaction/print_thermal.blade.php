<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk Transaksi</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt; /* Ukuran font standar struk */
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 5px; /* Sedikit padding agar tidak mepet */
            width: 58mm; /* Lebar kertas thermal 58mm */
        }
        @media print {
            @page {
                size: 58mm auto; /* Lebar 58mm, Tinggi Auto */
                margin: 0;
            }
            body {
                width: 58mm;
                margin: 0;
            }
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        .items { width: 100%; border-collapse: collapse; }
        .items td { padding: 2px 0; vertical-align: top; }
        
        /* Utility untuk menyembunyikan elemen saat dicetak jika perlu */
        .no-print { display: none; }
    </style>
</head>
<body onload="window.print();">

    <div class="text-center bold">
        JAYA ABADI<br>
        Rambipuji - Jember
    </div>
    <div class="line"></div>

    <table style="width: 100%">
        <tr>
            <td>No</td>
            <td>: {{ $transaction->no_transaksi }}</td>
        </tr>
        <tr>
            <td>Tgl</td>
            <td>: {{ date('d/m/y H:i', strtotime($transaction->created_at)) }}</td>
        </tr>
        <tr>
            <td>Plg</td>
            <td>: {{ $transaction->customer->nama ?? 'Umum' }}</td>
        </tr>
        <tr>
            <td>Ksr</td>
            <td>: {{ $transaction->user->name ?? '-' }}</td>
        </tr>
    </table>
    <div class="line"></div>

    <table class="items">
        @foreach($transaction->details as $detail)
        <tr>
            <td colspan="2">{{ $detail->product->nama_produk }}</td>
        </tr>
        <tr>
            <td class="text-left">
                {{ (float)$detail->jumlah }} x {{ number_format($detail->harga_satuan, 0, ',', '.') }}
            </td>
            <td class="text-right">
                {{ number_format($detail->subtotal, 0, ',', '.') }}
            </td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table style="width: 100%">
        <tr>
            <td class="text-right">Total :</td>
            <td class="text-right">{{ number_format($transaction->total_harga, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->bayar > 0)
        <tr>
            <td class="text-right">Bayar :</td>
            <td class="text-right">{{ number_format($transaction->bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-right bold">Kembali:</td>
            <td class="text-right bold">{{ number_format($transaction->kembalian, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="line"></div>
    <div class="text-center">
        Terima Kasih<br>
        <small>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</small>
    </div>
    <br>
    
    <!-- Script tambahan untuk auto close di beberapa browser -->
    <script>
        // Fallback jika onafterprint tidak jalan di mobile/browser tertentu
        // setTimeout(function() { window.close(); }, 3000); 
    </script>
</body>
</html>
