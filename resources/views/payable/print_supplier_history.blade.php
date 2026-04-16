<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Hutang - {{ $supplier->nama }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .meta-table td { padding: 5px; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-success { color: green; font-weight: bold; }
        .badge-danger { color: red; font-weight: bold; }
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
            <h2>RIWAYAT HUTANG SUPLIER</h2>
            <p>Demo APP - Jl. Dummy No. 123, Kota Dummy</p>
        </div>

        <table class="meta-table">
            <tr>
                <td width="15%"><strong>Suplier</strong></td>
                <td width="2%">:</td>
                <td width="33%">{{ $supplier->nama }}</td>
                <td width="15%"><strong>Total Sisa Hutang</strong></td>
                <td width="2%">:</td>
                <td width="33%">Rp {{ number_format($purchases->sum('remaining_debt'), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Alamat</strong></td>
                <td>:</td>
                <td>{{ $supplier->alamat ?? '-' }}</td>
                <td><strong>No Telepon</strong></td>
                <td>:</td>
                <td>{{ $supplier->telepon ?? '-' }}</td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No Faktur</th>
                    <th class="text-right">Total Tagihan</th>
                    <th class="text-right">Sudah Bayar</th>
                    <th class="text-right">Sisa Hutang</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d/m/Y', strtotime($trx->tanggal)) }}</td>
                    <td>{{ $trx->no_faktur }}</td>
                    <td class="text-right">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($trx->bayar, 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp {{ number_format($trx->remaining_debt, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($trx->remaining_debt > 0)
                            <span class="badge-danger">BELUM LUNAS</span>
                        @else
                            <span class="badge-success">LUNAS</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

         <div class="footer">
            <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
