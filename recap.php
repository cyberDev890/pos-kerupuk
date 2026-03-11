<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$transactions = \App\Models\Transaction::whereDate('tanggal', '>=', '2026-02-01')
    ->whereDate('tanggal', '<=', '2026-03-31')
    ->with('details.product.unit')
    ->get();

$totalRevenue = 0;
$totalCOGS = 0;

foreach ($transactions as $trx) {
    $trxNetRevenue = $trx->total_harga - $trx->biaya_kirim - $trx->biaya_tambahan;
    $totalRevenue += $trxNetRevenue;
    
    $trxCOGS = 0;
    foreach ($trx->details as $d) {
        if ($d->hpp !== null && $d->conversion !== null) {
            $cogs = $d->jumlah * $d->conversion * $d->hpp;
        } else {
            $p = $d->product;
            if (!$p) { $cogs = 0; }
            else {
                $qtyInPcs = $d->jumlah;
                if ($p->unit && $p->unit->isi > 1) {
                    $priceBesar = $p->harga_jual_besar ?? ($p->harga_jual * $p->unit->isi);
                    $threshold = ($priceBesar + $p->harga_jual) / 2;
                    if ($priceBesar > 0 && $d->harga_satuan >= $threshold) {
                        $qtyInPcs = $d->jumlah * $p->unit->isi;
                    }
                }
                $cogs = $qtyInPcs * $p->harga_beli;
            }
        }
        $trxCOGS += $cogs;
    }
    $totalCOGS += $trxCOGS;
}

echo "FINAL RECAP:\n";
echo "Total Revenue: ".number_format($totalRevenue)."\n";
echo "Total COGS: ".number_format($totalCOGS)."\n";
echo "Gross Profit: ".number_format($totalRevenue - $totalCOGS)."\n";
echo "Profit Margin: ".number_format(($totalRevenue > 0 ? ($totalRevenue - $totalCOGS)/$totalRevenue*100 : 0), 2)."%\n";
