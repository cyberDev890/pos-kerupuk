<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk Pembelian</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 5px;
            width: 58mm;
        }
        @media print {
            @page {
                size: 58mm auto;
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
    </style>
</head>
<body onload="window.print();">

    <div class="text-center bold">
        STRUK PEMBELIAN<br>
        JAYA ABADI
    </div>
    <div class="line"></div>

    <table style="width: 100%">
        <tr>
            <td>No Fak</td>
            <td>: {{ $purchase->no_faktur }}</td>
        </tr>
        <tr>
            <td>Tgl</td>
            <td>: {{ date('d/m/y H:i', strtotime($purchase->tanggal)) }}</td>
        </tr>
        <tr>
            <td>Spl</td>
            <td>: {{ $purchase->supplier->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Ptg</td>
            <td>: {{ $purchase->user->name ?? '-' }}</td>
        </tr>
    </table>
    <div class="line"></div>

    <table class="items">
        @foreach($purchase->details as $detail)
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

    <div class="text-right bold">
        GRAND TOTAL: {{ number_format($purchase->total_harga, 0, ',', '.') }}
    </div>

    <div class="line"></div>
    <br>
</body>
</html>
