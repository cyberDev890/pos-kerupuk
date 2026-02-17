<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier'])->withCount('details')->orderByDesc('id')->get();
        return view('purchase.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::with('unit')->where('is_active', 1)->get();
        
        // Generate Auto Code
        $today = date('Ymd');
        $prefix = 'TRB-' . $today;
        $lastPurchase = Purchase::where('no_faktur', 'like', "$prefix%")->orderByDesc('id')->first();
        
        if ($lastPurchase) {
            $lastNo = substr($lastPurchase->no_faktur, -4);
            $newNo = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNo = '0001';
        }
        
        $no_faktur = $prefix . '-' . $newNo;

        return view('purchase.create', compact('suppliers', 'products', 'no_faktur'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal' => 'required|date',
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.unit_id' => 'required|exists:units,id',
            'cart.*.jumlah' => 'required|numeric|min:1',
            'cart.*.harga_satuan' => 'required|numeric|min:0',
        ]);

        $total_harga = 0;
        foreach ($request->cart as $item) {
            $total_harga += $item['jumlah'] * $item['harga_satuan'];
        }

        $bayar = $request->input('bayar');
        if($bayar === null || $bayar === '') {
            $bayar = $total_harga;
        }

        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'tanggal' => $request->tanggal,
            'no_faktur' => $request->no_faktur,
            'keterangan' => $request->keterangan,
            'total_harga' => $total_harga,
            'bayar' => $bayar,
            'remaining_debt' => $total_harga - $bayar,
            'status' => $bayar < $total_harga ? 'pending' : 'selesai',
            'user_id' => auth()->id(),
        ]);

        // Record Payment if any
        if ($bayar > 0) {
            \App\Models\PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'amount' => $bayar,
                'payment_date' => $request->tanggal,
                'note' => 'Pembayaran Awal',
                'user_id' => auth()->id(),
            ]);
        }

        foreach ($request->cart as $item) {
            // ... (rest of the logic remains same)
            $unit = Unit::find($item['unit_id']);
            $isi = $unit ? $unit->isi : 1;
            
            $unitInfo = null;
            if ($unit) {
                $unitInfo = "1 {$unit->satuan_besar} = {$unit->isi} {$unit->satuan_kecil}";
            }

            // Simpan Detail
            PurchaseDetail::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'unit_id' => $item['unit_id'],
                'jumlah' => $item['jumlah'],
                'harga_satuan' => $item['harga_satuan'],
                'subtotal' => $item['jumlah'] * $item['harga_satuan'],
                'unit_info' => $unitInfo,
            ]);

            // Update Stok Gudang Produk (New Logic: Purchase -> Gudang)
            $product = Product::find($item['product_id']);
            $product->increment('stok_gudang', $item['jumlah'] * $isi);
            
            // Update Harga Beli Produk (Last Purchase Price converted to smallest unit)
            if ($item['harga_satuan'] > 0) {
                 $product->update(['harga_beli' => $item['harga_satuan'] / $isi]);
            }
        }

        toast()->success('Pembelian berhasil disimpan.');
        return redirect()->route('transaction.purchase.index');
    }

    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier', 
            'details.product' => function ($query) {
                $query->withTrashed();
            },
            'details.product.unit', 
            'details.unit', 
            'user'
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($purchase);
        }

        return view('purchase.show', compact('purchase'));
    }
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::with('details')->findOrFail($id);

            // Restore Stock (Decrease)
            foreach ($purchase->details as $detail) {
                // Determine qty in Pcs
                $qtyInPcs = $detail->jumlah;
                
                // Lookup unit isi from product or detail (if we stored it, but we stored unit_id)
                // We need to match the unit used in purchase detail to get 'isi'
                $unit = Unit::find($detail->unit_id);
                if($unit) {
                    $qtyInPcs = $detail->jumlah * $unit->isi;
                }

                $product = Product::withTrashed()->find($detail->product_id);
                if ($product) {
                    $product->decrement('stok_gudang', $qtyInPcs);
                }
            }

            // Soft Delete Purchase
            $purchase->delete();

            DB::commit();

            return redirect()->route('transaction.purchase.index')->with('success', 'Data pembelian berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menghapus pembelian: ' . $e->getMessage());
        }
    }

    public function openingBalance()
    {
        $suppliers = Supplier::all();
        
        // Generate Auto Code for Opening Balance
        $today = date('Ymd');
        $prefix = 'HUT-' . $today;
        $lastPurchase = Purchase::where('no_faktur', 'like', "$prefix%")->orderByDesc('id')->first();
        
        if ($lastPurchase) {
            $lastNo = substr($lastPurchase->no_faktur, -4);
            $newNo = str_pad($lastNo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNo = '0001';
        }
        
        $no_faktur = $prefix . '-' . $newNo;

        return view('purchase.opening-balance', compact('suppliers', 'no_faktur'));
    }

    public function storeOpeningBalance(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'tanggal' => 'required|date',
            'total_hutang' => 'required|numeric|min:1',
            'no_faktur' => 'required|unique:purchases,no_faktur',
        ]);

        $total_hutang = $request->total_hutang;

        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'tanggal' => $request->tanggal,
            'no_faktur' => $request->no_faktur,
            'keterangan' => $request->keterangan ?? 'Saldo Awal Hutang',
            'total_harga' => $total_hutang,
            'bayar' => 0,
            'remaining_debt' => $total_hutang,
            'status' => 'pending',
            'user_id' => auth()->id(),
        ]);

        toast()->success('Saldo awal hutang berhasil disimpan.');
        return redirect()->route('transaction.purchase.index');
    }
}
