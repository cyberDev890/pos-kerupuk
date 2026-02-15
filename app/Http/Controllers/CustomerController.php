<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPrice;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        confirmDelete('Hapus Data', 'Yakin ingin menghapus data pelanggan ini?');
        return view('customer.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        Customer::create($request->all());
        toast()->success('Data pelanggan berhasil disimpan.');
        return redirect()->route('master-data.customer.index');
    }

    public function update(Request $request, Customer $customer)
    {
         $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $customer->update($request->all());
        toast()->success('Data pelanggan berhasil diperbarui.');
        return redirect()->route('master-data.customer.index');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        toast()->success('Data pelanggan berhasil dihapus.');
        return redirect()->route('master-data.customer.index');
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        
        $products = \App\Models\Product::leftJoin('customer_prices', function($join) use ($id) {
            $join->on('products.id', '=', 'customer_prices.product_id')
                 ->where('customer_prices.customer_id', '=', $id);
        })
        ->leftJoin('units', 'products.unit_id', '=', 'units.id')
        ->select(
            'products.id', 
            'products.nama_produk', 
            'products.harga_jual', 
            'products.harga_jual_besar',
            'products.harga_beli',
            'units.isi',
            'units.nama_satuan',
            'units.satuan_kecil',
            'units.satuan_besar',
            'customer_prices.harga_jual as khusus_kecil',
            'customer_prices.harga_jual_besar as khusus_besar'
        )
        ->whereNull('products.deleted_at')
        ->where('products.is_active', 1)
        ->get();

        return view('customer.show', compact('customer', 'products'));
    }

    public function getPrices($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Get all active products
        // Left join with customer_prices filtered by this customer
        $products = \App\Models\Product::leftJoin('customer_prices', function($join) use ($id) {
            $join->on('products.id', '=', 'customer_prices.product_id')
                 ->where('customer_prices.customer_id', '=', $id);
        })
        ->leftJoin('units', 'products.unit_id', '=', 'units.id')
        ->select(
            'products.id', 
            'products.nama_produk', 
            'products.harga_jual', 
            'products.harga_jual_besar',
            'products.harga_beli',
            'units.isi',
            'units.nama_satuan',
            'units.satuan_kecil',
            'units.satuan_besar',
            'customer_prices.harga_jual as khusus_kecil',
            'customer_prices.harga_jual_besar as khusus_besar'
        )
        ->whereNull('products.deleted_at')
        ->where('products.is_active', 1)
        ->get();

        return response()->json($products);
    }

    public function storePrices(Request $request, $id)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*.product_id' => 'required|exists:products,id',
            'prices.*.harga_jual' => 'nullable|string', 
            'prices.*.harga_jual_besar' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($id);
        
        foreach ($request->prices as $price) {
            $hargaKecil = $price['harga_jual'] ? str_replace('.', '', $price['harga_jual']) : null;
            $hargaBesar = $price['harga_jual_besar'] ? str_replace('.', '', $price['harga_jual_besar']) : null;
            
            // If both are empty, delete the record to save space and use default
            if (empty($hargaKecil) && empty($hargaBesar)) {
                CustomerPrice::where('customer_id', $id)
                    ->where('product_id', $price['product_id'])
                    ->delete();
            } else {
                CustomerPrice::updateOrCreate(
                    ['customer_id' => $id, 'product_id' => $price['product_id']],
                    [
                        'harga_jual' => $hargaKecil,
                        'harga_jual_besar' => $hargaBesar
                    ]
                );
            }
        }

        return redirect()->route('master-data.customer.show', $id)->with('success', 'Harga khusus berhasil disimpan.');
    }
}
