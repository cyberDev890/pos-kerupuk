<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TransactionDetail;

$details = TransactionDetail::with('product.unit')->get();

echo "Repairing HPP and Conversion for " . $details->count() . " details...\n";

$updatedCount = 0;
foreach ($details as $d) {
    if (!$d->product) continue;
    $p = $d->product;
    $u = $p->unit;
    
    $oldConv = $d->conversion;
    $oldHpp = $d->hpp;
    
    $newConv = 1;
    $newHpp = $p->harga_beli; 
    
    if ($u && $u->isi > 1) {
        $priceSmall = $p->harga_jual;
        $priceBesar = $p->harga_jual_besar ?? ($priceSmall * $u->isi);
        
        $isBesar = false;
        
        // 1. By Unit Info String (Most Robust)
        if (!empty($d->unit_info) && (stripos($d->unit_info, 'Bal') !== false || stripos($d->unit_info, 'Besar') !== false)) {
            $isBesar = true;
        }
        
        // 2. By Price Proximity to Besar Price
        if (!$isBesar && $priceBesar > 0) {
            $diffBesar = abs($d->harga_satuan - $priceBesar);
            if ($diffBesar < ($priceBesar * 0.4)) { // 40% margin
                $isBesar = true;
            }
        }
        
        // 3. By Threshold (Original Logic)
        if (!$isBesar && $priceBesar > $priceSmall && $priceSmall > 0) {
             $threshold = ($priceBesar + $priceSmall) / 2;
             if ($d->harga_satuan >= $threshold) {
                 $isBesar = true;
             }
        }
        
        if ($isBesar) {
            $newConv = $u->isi;
        }
    }
    
    if ($oldConv != $newConv || $oldHpp != $newHpp) {
        $d->update([
            'conversion' => $newConv,
            'hpp' => $newHpp,
            'unit_type' => ($newConv > 1 ? 'besar' : 'kecil')
        ]);
        $updatedCount++;
    }
}

echo "Updated $updatedCount records.\n";
