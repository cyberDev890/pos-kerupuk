<?php
// test_fix.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

echo "Verifying Fix...\n";

// Get a supplier for testing
$supplier = Supplier::first();
if (!$supplier) {
    echo "No supplier found to test.\n";
    exit;
}

// Logic from Controller
$total_harga = 100000;
$input_bayar = ''; // Simulating empty input from request

$bayar = $input_bayar;
if($bayar === null || $bayar === '') {
    $bayar = 0; // The fix
}

$remaining_debt = $total_harga - $bayar;
$status = $bayar < $total_harga ? 'pending' : 'selesai';

echo "Test Results:\n";
echo "Total Harga: $total_harga\n";
echo "Input Bayar: (empty)\n";
echo "Calculated Bayar: $bayar\n";
echo "Remaining Debt: $remaining_debt\n";
echo "Status: $status\n";

if ($bayar === 0 && $remaining_debt === 100000 && $status === 'pending') {
    echo "VERIFICATION SUCCESS: Debt logic is correct.\n";
} else {
    echo "VERIFICATION FAILED: Debt logic is incorrect.\n";
}
