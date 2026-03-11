<?php
// check_all_purchases.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;

$allPurchases = Purchase::withTrashed()->get();

echo "Total (including trashed): " . $allPurchases->count() . "\n";
foreach ($allPurchases as $p) {
    echo "ID: {$p->id} | No: {$p->no_faktur} | Total: {$p->total_harga} | Paid: {$p->bayar} | Debt: {$p->remaining_debt} | Trashed: " . ($p->deleted_at ? 'YES' : 'NO') . "\n";
}
