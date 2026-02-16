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
        // QZ Tray Mode: Server generates commands, Client prints them.
        $transaction = \App\Models\Transaction::with(['details.product', 'customer', 'user'])->findOrFail($id);

        try {
            // Use DummyConnector to generate RAW commands in memory
            $connector = new \Mike42\Escpos\PrintConnectors\DummyPrintConnector();
            $printer = new \Mike42\Escpos\Printer($connector);

            // Initialize
            $printer->initialize();
            
            // Layout (Sama persis dengan sebelumnya)
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->text("JAYA ABADI\n");
            $printer->text("Jl. Ijen Dukusia Rambipuji\n");
            $printer->text("082330634269\n");
            $printer->text("-----------------------------\n"); // 29 Chars

            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $printer->text("No  : " . $transaction->no_transaksi . "\n");
            $printer->text("Tgl : " . date('d/m/Y H:i', strtotime($transaction->created_at)) . "\n");
            $printer->text("Csr : " . ($transaction->user->name ?? '-') . "\n");
            $printer->text("Plg : " . ($transaction->customer->nama ?? 'Umum') . "\n");
            $printer->text("-----------------------------\n");

            foreach ($transaction->details as $detail) {
                $printer->text($detail->product->nama_produk . "\n");
                $qtyDisplay = (float)$detail->jumlah; 
                $line = $qtyDisplay . "x " . number_format($detail->harga_satuan, 0, ',', '.') . " = " . number_format($detail->subtotal, 0, ',', '.');
                $printer->text($line . "\n");
            }

            $printer->text("-----------------------------\n");
            
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_RIGHT);
            $printer->text("Total: " . number_format($transaction->total_harga, 0, ',', '.') . "\n");
            
            if($transaction->biaya_kirim > 0) {
                $printer->text("Ongkir: " . number_format($transaction->biaya_kirim, 0, ',', '.') . "\n");
            }
            if($transaction->biaya_tambahan > 0) {
                $printer->text("Lainnya: " . number_format($transaction->biaya_tambahan, 0, ',', '.') . "\n");
            }

            $printer->setEmphasis(true);
            $printer->text("Grand Total: " . number_format($transaction->grand_total, 0, ',', '.') . "\n");
            $printer->setEmphasis(false);
            
            $printer->text("Bayar: " . number_format($transaction->bayar, 0, ',', '.') . "\n");
            $printer->text("Kembali: " . number_format($transaction->kembalian, 0, ',', '.') . "\n");

            $printer->text("-----------------------------\n");
            $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $printer->text("Terima Kasih\n");
            
            $printer->feed(3); 
            $printer->cut();   
            
            // Get the binary data
            $data = $connector->getData();
            $printer->close();
            
            // Return base64 encoded data
            return response()->json([
                'success' => true, 
                'data' => base64_encode($data)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Gagal Generate Struk: " . $e->getMessage()], 500);
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
        $path = storage_path('app/qz/digital-certificate.txt');
        if(!file_exists($path)) return response("Certificate not found", 404);
        return response()->file($path, ['Content-Type' => 'text/plain']);
    }

    public function qzSign(Request $request) 
    {
        $requestData = $request->input('request'); // The data to sign
        $privateKeyPath = storage_path('app/qz/private-key.pem');
        
        if(!file_exists($privateKeyPath)) {
            \Log::error("QZ Sign: Private Key not found at " . $privateKeyPath);
            return response("Private Key not found", 404);
        }

        // Use openssl_pkey_get_private to handle formatting (CRLF etc) safely
        $privateKeyContent = file_get_contents($privateKeyPath);
        $privateKey = openssl_pkey_get_private($privateKeyContent);

        if (!$privateKey) {
            \Log::error("QZ Sign: Invalid Private Key Format");
            return response("Invalid Private Key", 500);
        }
        
        $signature = null;
        if (openssl_sign($requestData, $signature, $privateKey, "sha512")) { 
            return base64_encode($signature);
        }
        
        \Log::error("QZ Sign Failed: " . openssl_error_string());
        return response("Failed to sign: " . openssl_error_string(), 500);
    }
    public function setupQZ()
    {
        return view('transaction.setup-qz');
    }

    public function downloadCA()
    {
        $path = storage_path('app/qz/root-ca.crt');
        if(!file_exists($path)) {
            return back()->with('error', 'CA Certificate belum dibuat. Jalankan command artisan qz:generate-keys dulu.');
        }
        return response()->download($path, 'JayaAbadi-POS-RootCA.crt');
    }
}
