<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran Piutang</title>
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
        .title { font-weight: bold; margin-bottom: 5px; display: block; }
    </style>
</head>
<body onload="window.print();">

    <div class="text-center bold">
        PEMBAYARAN PIUTANG<br>
        JAYA ABADI
    </div>
    <div class="line"></div>

    <table style="width: 100%">
        <tr>
            <td>No Trx</td>
            <td>: {{ $transaction->no_transaksi }}</td>
        </tr>
        <tr>
            <td>Plg</td>
            <td>: {{ $transaction->customer->nama ?? 'Umum' }}</td>
        </tr>
    </table>
    <div class="line"></div>

    <span class="title">Riwayat Pembayaran:</span>
    <table class="items">
        @foreach($transaction->payments as $pay)
        <tr>
            <td>{{ date('d/m/y', strtotime($pay->payment_date)) }}</td>
            <td class="text-right">{{ number_format($pay->amount, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table style="width: 100%">
        <tr>
            <td class="text-right">Total Bayar:</td>
            <td class="text-right">{{ number_format($transaction->payments->sum('amount'), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-right bold">SISA PIUTANG:</td>
            <td class="text-right bold">{{ number_format($transaction->remaining_debt, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>
    <br>
    
    <script>
        setTimeout(function() { window.close(); }, 3000); 
    </script>
</body>
</html>
