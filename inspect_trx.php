<?php
$trx = \App\Models\Transaction::where('no_transaksi', 'TRX-20260215-0032')->with('details.product.unit')->first();
if ($trx) {
    foreach($trx->details as $d) {
        echo "Product: " . $d->product->nama_produk . "\n";
        echo "Harga Satuan (Deal): " . number_format($d->harga_satuan) . "\n";
        echo "Jumlah: " . $d->jumlah . "\n";
        echo "Unit ID (Detail): " . ($d->unit_id ?? 'NULL') . "\n";
        echo "Product Unit ID: " . $d->product->unit_id . "\n";
        echo "Small Price: " . number_format($d->product->harga_jual) . "\n";
        echo "Big Price: " . number_format($d->product->harga_jual_besar) . "\n";
        echo "Unit Isi: " . $d->product->unit->isi . "\n";
    }
} else {
    echo "Transaction not found.\n";
}
