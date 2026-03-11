<?php
// dump_purchases.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;

$purchases = Purchase::with('supplier')->get();

foreach ($purchases as $p) {
    echo "ID: {$p->id}\n";
    echo "No Faktur: {$p->no_faktur}\n";
    echo "Date: {$p->tanggal}\n";
    echo "Supplier: " . ($p->supplier->nama ?? 'N/A') . "\n";
    echo "Total: {$p->total_harga}\n";
    echo "Bayar: {$p->bayar}\n";
    echo "Remaining Debt: {$p->remaining_debt}\n";
    echo "Status: {$p->status}\n";
    echo "Keterangan: {$p->keterangan}\n";
    echo "---------------------------\n";
}
