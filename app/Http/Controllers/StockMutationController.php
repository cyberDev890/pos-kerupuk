<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMutationController extends Controller
{
    public function index()
    {
        $mutations = \App\Models\StockMutation::with(['product', 'user'])->latest()->paginate(10);
        return view('stock.mutation.index', compact('mutations'));
    }

    public function create()
    {
        // Get products with potential stock in Gudang
        $products = Product::with('unit')->where('is_active', 1)->get();
        return view('stock.mutation.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::with('unit')->findOrFail($request->product_id);

            // Execution Logic
            $qty = $request->jumlah;
            $unitChoice = $request->unit_choice; // 'kecil' or 'besar'
            
            $conversion = 1;
            $unitLabel = $product->unit->satuan_kecil ?? 'Pcs';

            if ($unitChoice == 'besar') {
                $conversion = $product->unit->isi ?? 1;
                $unitLabel = $product->unit->satuan_besar ?? 'Bal';
            }

            $qtyInPcs = $qty * $conversion;
            
            // Validation: Zero Stock or Insufficient Stock
            if ($product->stok_gudang <= 0) {
                 return back()->with('error', "Stok Gudang Kosong! Tidak bisa melakukan mutasi.");
            }

            if ($product->stok_gudang < $qtyInPcs) {
                return back()->with('error', "Stok Gudang tidak cukup! (Tersedia: {$product->stok_gudang} Pcs, Diminta: {$qtyInPcs} Pcs)");
            }

            // Execute Mutation
            $product->decrement('stok_gudang', $qtyInPcs);
            $product->increment('stok', $qtyInPcs);

            // Record History
            \App\Models\StockMutation::create([
                'product_id' => $product->id,
                'amount' => $qtyInPcs,
                'unit_info' => "$qty $unitLabel",
                'source' => 'gudang',
                'destination' => 'toko',
                'user_id' => auth()->id(),
                'notes' => 'Mutasi Manual Web',
            ]);

            DB::commit();

            return redirect()->route('stock.mutation.index')->with('success', "Mutasi Berhasil! {$qtyInPcs} Pcs dipindah ke Toko.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal mutasi: ' . $e->getMessage());
        }
    }
}
