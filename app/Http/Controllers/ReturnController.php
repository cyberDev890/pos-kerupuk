<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\ReturnDetail;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index()
    {
        $returns = ProductReturn::with(['customer', 'supplier', 'user', 'transaction', 'purchase'])
                    ->orderByDesc('id')
                    ->get();
        return view('return.index', compact('returns'));
    }

    public function create()
    {
        $customers = Customer::all();
        $suppliers = Supplier::all();
        $products = Product::with('unit')->where('is_active', 1)->get();
        return view('return.create', compact('customers', 'suppliers', 'products'));
    }

    public function searchTransaction(Request $request)
    {
        $type = $request->input('type'); // penjualan / pembelian
        $term = $request->input('q');

        // if (!$term) return response()->json([]); // Allow empty term for default list

        if ($type == 'penjualan') {
            $query = \App\Models\Transaction::with(['customer', 'details.product.unit', 'details.unit']);
            
            if ($term) {
                $query->where('no_transaksi', 'like', "%$term%");
            }
            
            $data = $query->orderByDesc('id')
                ->limit(10)
                ->get();
            
            return response()->json($data->map(function($trx) {
                return [
                    'id' => $trx->id,
                    'text' => $trx->no_transaksi . ' - ' . ($trx->customer->nama ?? 'Umum') . ' (' . date('d/m/Y', strtotime($trx->tanggal)) . ')',
                    'customer_id' => $trx->customer_id,
                    'customer_name' => $trx->customer->nama ?? 'Umum',
                    'details' => $trx->details->map(function($detail) {
                        $product = $detail->product;
                        $unit = $product->unit;
                        
                        // Default to Small/Base
                        $unitName = $unit->satuan_kecil ?? 'Pcs';
                        $unitType = 'kecil';
                        $conversion = 1;

                        // Heuristic: Check if sold as "Besar" based on price
                        if ($unit && $unit->isi > 1) {
                             $priceBesar = $product->harga_jual_besar ?? ($product->harga_jual * $unit->isi);
                             
                             // If price matches Big Unit Price (with tolerance)
                             if (abs($detail->harga_satuan - $priceBesar) < 500) {
                                 $unitName = $unit->satuan_besar;
                                 $unitType = 'besar';
                                 $conversion = $unit->isi;
                             }
                        }

                        return [
                            'product_id' => $detail->product_id,
                            'nama_produk' => $product->nama_produk,
                            'unit_id' => $detail->unit_id,
                            'unit_name' => $unitName, // Return the detected unit name
                            'jumlah_beli' => $detail->jumlah,
                            'harga_satuan' => $detail->harga_satuan,
                            'unit_info' => $detail->unit_info,
                            'conversion' => $conversion,
                            'unit_type' => $unitType
                        ];
                    })
                ];
            }));
        } else {
            $query = \App\Models\Purchase::with(['supplier', 'details.product.unit', 'details.unit']);
            
            if ($term) {
                $query->where('no_faktur', 'like', "%$term%");
            }

            $data = $query->orderByDesc('id')
                ->limit(10)
                ->get();

            return response()->json($data->map(function($purch) {
                return [
                    'id' => $purch->id,
                    'text' => ($purch->no_faktur ?? '-') . ' - ' . $purch->supplier->nama_supplier . ' (' . date('d/m/Y', strtotime($purch->tanggal)) . ')',
                    'supplier_id' => $purch->supplier_id,
                    'supplier_name' => $purch->supplier->nama_supplier,
                    'details' => $purch->details->map(function($detail) {
                        $product = $detail->product;
                        $unit = $product->unit;
                        
                        // Default for Purchase: Prefer Besar/Bal if available, as requested
                        $unitName = $unit->satuan_kecil ?? 'Pcs';
                        $unitType = 'kecil';
                        $conversion = 1;

                        if ($unit && $unit->isi > 1) {
                            // For purchase, usually we assume it's the larger unit (BAL)
                            // But we should check if the price makes sense? 
                            // User said: "retur pembelian ... perball bukan per pcs"
                            // So let's Force Besar if available
                             $unitName = $unit->satuan_besar;
                             $unitType = 'besar';
                             $conversion = $unit->isi;
                        }

                        return [
                            'product_id' => $detail->product_id,
                            'nama_produk' => $product->nama_produk,
                            'unit_id' => $detail->unit_id,
                            'unit_name' => $unitName,
                            'jumlah_beli' => $detail->jumlah,
                            'harga_satuan' => $detail->harga_satuan,
                            'unit_info' => $detail->unit_info ?? '',
                            'conversion' => $conversion,
                            'unit_type' => $unitType
                        ];
                    })
                ];
            }));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_retur' => 'required|in:penjualan,pembelian',
            // 'cart' => 'required|array|min:1', // Cart validation moved below or handled
        ]);
        
        if (empty($request->cart)) {
             return back()->with('error', 'Item retur belum dipilih!');
        }

        try {
            DB::beginTransaction();

            $total_harga = 0;
            $cartItems = [];

            // Identify Parent Transaction/Purchase
            $transactionId = $request->transaction_id;
            $purchaseId = $request->purchase_id;

            // Fetch Parent for validation (Optional step, but good safety)
            // ...

            foreach ($request->cart as $item) {
                $product = Product::with('unit')->find($item['product_id']);
                
                // Conversion Logic
                // Frontend should send 'conversion' factor.
                // If not sent, we default to 1.
                $conversion = $item['conversion'] ?? 1;
                $qty = $item['jumlah'];
                $qtyInPcs = $qty * $conversion;

                $harga_satuan = $item['harga_satuan'] ?? 0;
                $subtotal = $qty * $harga_satuan;
                $total_harga += $subtotal;

                $cartItems[] = [
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? $product->unit_id, // Fallback
                    'jumlah' => $qty,
                    'harga_satuan' => $harga_satuan,
                    'subtotal' => $subtotal,
                    'unit_info' => $item['unit_info'] ?? null,
                    'conversion' => $conversion,
                    'unit_type' => $item['unit_type'] ?? null,
                ];

                // Stock Adjustment
                if ($request->jenis_retur == 'penjualan') {
                    // Sales Return: Customer returns item -> Stock Increases
                    $product->increment('stok', $qtyInPcs);
                } else {
                    // Purchase Return: We return item to Supplier -> Stock Decreases
                    $product->decrement('stok', $qtyInPcs);
                }
            }

            $productReturn = ProductReturn::create([
                'tanggal' => $request->tanggal,
                'jenis_retur' => $request->jenis_retur,
                'customer_id' => $request->customer_id,
                'supplier_id' => $request->supplier_id,
                'transaction_id' => $transactionId, // Store Link
                'purchase_id' => $purchaseId,       // Store Link
                'total_harga' => $total_harga,
                'keterangan' => $request->keterangan,
                'user_id' => auth()->id(),
            ]);

            foreach ($cartItems as $item) {
                ReturnDetail::create([
                    'product_return_id' => $productReturn->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $item['subtotal'],
                    'unit_info' => $item['unit_info'],
                    'conversion' => $item['conversion'], // Store strictly
                    'unit_type' => $item['unit_type'],
                ]);
            }

            DB::commit();
            return redirect()->route('return.index')->with('success', 'Retur berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $productReturn = ProductReturn::with('details')->findOrFail($id);

            // Reverse Stock
            foreach ($productReturn->details as $detail) {
                $product = Product::withTrashed()->find($detail->product_id);
                if ($product) {
                    $conversion = $detail->conversion; // Use stored conversion!
                    // Fallback for old data if conversion is null (though we set default 1)
                    if(!$conversion || $conversion <= 0) $conversion = 1;

                    $qtyInPcs = $detail->jumlah * $conversion;
                    
                    if ($productReturn->jenis_retur == 'penjualan') {
                        // Was Sales Return (Stock Inc). Undo -> Stock Dec
                        $product->decrement('stok', $qtyInPcs);
                    } else {
                        // Was Purchase Return (Stock Dec). Undo -> Stock Inc
                        $product->increment('stok', $qtyInPcs);
                    }
                }
            }

            $productReturn->delete();
            DB::commit();
            
            return redirect()->route('return.index')->with('success', 'Data retur berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus retur: ' . $e->getMessage());
        }
    }
}
