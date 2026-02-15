<?php
$startDate = '2026-02-01';
$endDate = '2026-02-15';

$transactions = \App\Models\Transaction::with(['details.product.unit', 'customer'])
    ->whereDate('tanggal', '>=', $startDate)
    ->whereDate('tanggal', '<=', $endDate)
    ->get();

$totalCOGS = 0;
echo "--- DETAIL PENJUALAN ---\n";
foreach ($transactions as $trx) {
    foreach ($trx->details as $detail) {
        $product = $detail->product;
        if (!$product) continue;
        $unit = $product->unit;
        
        $qtyInPcs = $detail->jumlah;
        $isBesar = false;

        if ($unit && $unit->isi > 1) {
             $priceSmall = $product->harga_jual;
             $priceBesar = $product->harga_jual_besar ?? ($priceSmall * $unit->isi);
             
             // Logic: Threshold = Midpoint between Small and Big Price
             $threshold = ($priceBesar + $priceSmall) / 2;

             if ($priceBesar > 0 && $detail->harga_satuan >= $threshold) {
                 $qtyInPcs = $detail->jumlah * $unit->isi;
                 $isBesar = true;
             }
             elseif ($priceBesar > 0 && abs($detail->harga_satuan - $priceBesar) < ($priceBesar * 0.2)) {
                 $qtyInPcs = $detail->jumlah * $unit->isi;
                 $isBesar = true;
             }
        }
        
        $cost = $qtyInPcs * $product->harga_beli;
        $totalCOGS += $cost;
        
        echo "TRX {$trx->no_transaksi} | {$product->nama_produk} | Qty: {$detail->jumlah} " . ($isBesar ? '(Besar)' : '(Kecil)') . " | Cost: " . number_format($cost) . "\n";
    }
}

echo "\n--- DETAIL RETUR ---\n";
$returns = \App\Models\ProductReturn::with(['details.product'])
    ->where('jenis_retur', 'penjualan')
    ->whereDate('tanggal', '>=', $startDate)
    ->whereDate('tanggal', '<=', $endDate)
    ->get();

$totalReturnCOGS = 0;
foreach ($returns as $ret) {
    echo "RETUR {$ret->no_retur}\n";
    foreach ($ret->details as $detail) {
        $product = $detail->product;
        if (!$product) continue;

         $conversion = $detail->conversion ?? 1;
         if ($conversion <= 0) $conversion = 1;

         $qtyInPcs = $detail->jumlah * $conversion;
         $cost = $qtyInPcs * $product->harga_beli;
         $totalReturnCOGS += $cost;
         
         echo "  - {$product->nama_produk} | Qty: {$detail->jumlah} (Conv: {$conversion}) | Cost: " . number_format($cost) . "\n";
    }
}

echo "\n--- REKAP ---\n";
echo "Total Modal Terjual: " . number_format($totalCOGS) . "\n";
echo "Total Modal Retur: " . number_format($totalReturnCOGS) . "\n";
echo "HPP Net: " . number_format($totalCOGS - $totalReturnCOGS) . "\n";
