<?php
// check_purchases.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;
use App\Models\Supplier;

$totalPurchases = Purchase::count();
$withDebt = Purchase::where('remaining_debt', '>', 0)->count();
$zeroDebt = Purchase::where('remaining_debt', '=', 0)->count();

echo "Total Purchases: $totalPurchases\n";
echo "Purchases with Debt: $withDebt\n";
echo "Purchases with Zero Debt: $zeroDebt\n";

if ($totalPurchases > 0) {
    echo "\nSample Purchases (Last 10):\n";
    $samples = Purchase::with('supplier')->orderByDesc('id')->limit(10)->get();
    foreach ($samples as $p) {
        echo "ID: {$p->id} | Date: {$p->tanggal} | Total: {$p->total_harga} | Paid: {$p->bayar} | Debt: {$p->remaining_debt} | Supplier: " . ($p->supplier->nama ?? 'N/A') . "\n";
    }
}

$suppliersWithDebt = Supplier::whereHas('purchases', function($q) {
    $q->where('remaining_debt', '>', 0);
})->get();

echo "\nSuppliers with active Debt: " . $suppliersWithDebt->count() . "\n";
foreach($suppliersWithDebt as $s) {
    echo "- {$s->nama}\n";
}
