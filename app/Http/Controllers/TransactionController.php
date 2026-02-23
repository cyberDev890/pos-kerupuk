<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['customer', 'user'])->orderByDesc('id')->get();
        return view('transaction.index', compact('transactions'));
    }

    public function create()
    {
        $customers = Customer::all();
        // Load active products with units
        // Load active products with units and stock > 0
        $products = Product::with('unit')
            ->where('is_active', 1)
            ->where('stok', '>', 0) // Best Practice: Hide out of stock items
            ->get();
        return view('transaction.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        // Sanitize
        $request->merge([
            'bayar' => $request->bayar ? str_replace('.', '', $request->bayar) : 0,
            'biaya_kirim' => $request->biaya_kirim ? str_replace('.', '', $request->biaya_kirim) : 0,
            'biaya_tambahan' => $request->biaya_tambahan ? str_replace('.', '', $request->biaya_tambahan) : 0,
        ]);

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'tanggal' => 'required|date',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.unit_id' => 'required|exists:units,id',
            'cart.*.jumlah' => 'required|numeric|min:0.01',
            'bayar' => 'required|numeric|min:0',
            'biaya_kirim' => 'nullable|numeric|min:0',
            'biaya_tambahan' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $total_harga = 0;
            $biaya_kirim = $request->biaya_kirim ?? 0;
            $biaya_tambahan = $request->biaya_tambahan ?? 0;
            $cartItems = [];

            // Calculate Total & Prepare Items
            foreach ($request->cart as $item) {
                $product = Product::with('unit')->find($item['product_id']);
                // ... (existing logic)
                
                $harga_satuan = $item['harga_satuan'];
                $subtotal = $item['jumlah'] * $harga_satuan;
                $total_harga += $subtotal;

                $unitInfo = null;
                if ($product->unit) {
                    $unitInfo = "1 {$product->unit->satuan_besar} = {$product->unit->isi} {$product->unit->satuan_kecil}";
                }
                
                $cartItems[] = [
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $harga_satuan,
                    'subtotal' => $subtotal,
                    'isi' => $item['conversion'] ?? 1, 
                    'unit_type' => $item['unit_type'] ?? 'kecil',
                    'unit_info' => $unitInfo
                ];
            }
            
            // Add Fees to Total
            $grand_total = $total_harga + $biaya_kirim + $biaya_tambahan;

            // Debt Logic
            $bayar = $request->bayar;
            $kembalian = 0;
            $remaining_debt = 0;
            $status = 'selesai';

            if ($bayar < $grand_total) {
                // Partial Payment (Debt)
                // Ensure we have a customer for debt
                if (!$request->customer_id) {
                     return back()->with('error', 'Transaksi hutang harus memilih pelanggan!');
                }
                
                $remaining_debt = $grand_total - $bayar;
                $status = 'pending'; // Or 'partial'
            } else {
                // Full Payment
                $kembalian = $bayar - $grand_total;
            }

            // Create Transaction
            $transaction = Transaction::create([
                // no_transaksi generated in boot
                'tanggal' => $request->tanggal,
                'customer_id' => $request->customer_id,
                'total_harga' => $grand_total,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
                'remaining_debt' => $remaining_debt,
                'biaya_kirim' => $biaya_kirim,
                'biaya_tambahan' => $biaya_tambahan,
                'status' => $status,
                'user_id' => auth()->id(),
            ]);

            // Record Initial Payment
            if($bayar > 0) {
                \App\Models\TransactionPayment::create([
                    'transaction_id' => $transaction->id,
                    'amount' => $bayar,
                    'payment_date' => now(),
                    'note' => 'Pembayaran Awal',
                    'user_id' => auth()->id()
                ]);
            }

            // Process Details & Stock
            foreach ($cartItems as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['subtotal'],
                    'unit_info' => $item['unit_info'],
                ]);

                // Update Stock
                // If unit_type is 'besar', reduce stok by jumlah * isi.
                // If 'kecil', reduce by jumlah.
                // I'll expect 'conversion' (isi) to be sent from frontend or looked up.
                // Since `unit_id` is just the Unit Definition, we need to know if we are selling the "Big" or "Small" version of that unit definition.
                // Actually `products` table has `unit_id`. 
                // So if we sell "Big", the decrement is `jumlah * unit->isi`.
                // If "Small", decrement is `jumlah`.
                
                $qtyInPcs = $item['jumlah'];
                if(isset($item['unit_type']) && $item['unit_type'] == 'besar') {
                     // lookup unit isi
                     $prod = Product::find($item['product_id']);
                     $qtyInPcs = $item['jumlah'] * $prod->unit->isi;
                }
                
                Product::find($item['product_id'])->decrement('stok', $qtyInPcs);
            }

            DB::commit();
            DB::commit();
            
            $message = 'Transaksi berhasil disimpan. Kembalian: Rp ' . number_format($kembalian);
            
            // Logic Print
            // If Customer is selected (Not Umum), show Print Option Modal
            // If Umum, just standard success (which triggers auto print in view)
            if ($request->customer_id) {
                return redirect()->route('transaction.sales.create')
                    ->with('success', $message)
                    ->with('last_transaction_id', $transaction->id)
                    ->with('show_print_modal', true);
            } else {
                return redirect()->route('transaction.sales.create')
                    ->with('success', $message)
                    ->with('last_transaction_id', $transaction->id);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $transaction = Transaction::with(['customer', 'user', 'details.product.unit', 'details.unit'])->findOrFail($id);
        return response()->json($transaction);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $transaction = Transaction::with('details')->findOrFail($id);
            
            // Restore Stock
            foreach ($transaction->details as $detail) {
                // Heuristic to detect if it was "Besar" unit
                // See store() logic: if unit_type was 'besar', we reduced by (qty * isi).
                // We didn't store unit_type, so we guess based on price.
                
                $product = Product::withTrashed()->find($detail->product_id);
                $qtyToRestore = $detail->jumlah;
                
                if ($product && $product->unit_id) {
                     $unit = $product->unit;
                     if($unit && $unit->isi > 1) {
                         // Check if the stored price is closer to Large Price
                         $priceBesar = $product->harga_jual_besar ?? ($product->harga_jual * $unit->isi);
                         
                         // Tolerance for float comparison (500 perak)
                         if (abs($detail->harga_satuan - $priceBesar) < 500) { 
                             // It matches Big Price logic
                             $qtyToRestore = $detail->jumlah * $unit->isi;
                         }
                     }
                }
                
                if($product) {
                    $product->increment('stok', $qtyToRestore);
                }
            }
            
            // Delete Detail & Header
            // Delete Header (Soft Delete)
            $transaction->delete();
            
            DB::commit();
            
            return redirect()->route('transaction.sales.index')->with('success', 'Transaksi berhasil dihapus.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $transaction = \App\Models\Transaction::with(['details.product', 'customer', 'user'])->findOrFail($id);
        return view('transaction.print', compact('transaction'));
    }

    public function printRaw($id)
    {
        // DIRECT PRINTING MODE: Server sends commands directly to a shared printer.
        // Important: Shared Printer name MUST be 'pos_printer' in Windows.
        $transaction = \App\Models\Transaction::with(['details.product', 'customer', 'user'])->findOrFail($id);

        try {
            // Browser Native Print: Return HTML View
            return view('transaction.print_thermal', compact('transaction'));

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Gagal memuat struk: " . $e->getMessage()], 500);
        }
    }

    public function invoice($id)
    {
        $transaction = \App\Models\Transaction::with(['details.product.unit', 'details.unit', 'customer', 'user'])->findOrFail($id);
        return view('transaction.invoice', compact('transaction'));
    }

    // QZ Tray Security
    public function qzCertificate()
    {
        try {
            $path = storage_path('app/qz/digital-certificate.txt');
            if(!file_exists($path)) return response("Certificate file not found", 404);
            return response()->file($path, ['Content-Type' => 'text/plain']);
        } catch (\Exception $e) {
            return response("Error reading cert: " . $e->getMessage(), 500);
        }
    }

    public function qzSign(Request $request) 
    {
        try {
            $requestData = $request->input('request'); 
            $privateKeyPath = storage_path('app/qz/private-key.pem');
            
            if(!file_exists($privateKeyPath)) {
                return response("Private Key not found at " . $privateKeyPath, 404);
            }
            
            if (!is_readable($privateKeyPath)) {
                 $perms = substr(sprintf('%o', fileperms($privateKeyPath)), -4);
                 return response("Private Key not readable. Perms: $perms", 500);
            }

            $privateKeyContent = file_get_contents($privateKeyPath);
            $privateKey = openssl_pkey_get_private($privateKeyContent);

            if (!$privateKey) {
                return response("Invalid Private Key Format", 500);
            }
            
            $signature = null;
            if (openssl_sign($requestData, $signature, $privateKey, "sha512")) { 
                return response(base64_encode($signature))->header('Content-Type', 'text/plain');
            }
            
            return response("Failed to sign: " . openssl_error_string(), 500);
        } catch (\Exception $e) {
             return response("Sign Exception: " . $e->getMessage(), 500);
        }
    }
    public function setupQZ()
    {
        return view('transaction.setup-qz');
    }

    public function downloadCA()
    {
        try {
            $path = storage_path('app/qz/root-ca.crt');
            
            if (!file_exists($path)) {
                return response("DEBUG: File not found at " . $path, 404);
            }
            
            // Debug Permissions
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $owner = posix_getpwuid(fileowner($path))['name'];
            
            // Manual Download Headers
            $content = file_get_contents($path);
            if ($content === false) {
                 return response("DEBUG: Failed to read file. Owner: $owner, Perms: $perms", 500);
            }

            return response($content)
                ->header('Content-Type', 'application/x-x509-ca-cert')
                ->header('Content-Disposition', 'attachment; filename="JayaAbadi-POS-RootCA.crt"');

        } catch (\Throwable $e) {
            return response("DEBUG EXCEPTION: " . $e->getMessage() . " on line " . $e->getLine(), 500);
        }
    }
}
