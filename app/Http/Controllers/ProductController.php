<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //

    public function index()
    {

        $products = Product::all();
        confirmDelete('Hapus Data', 'Yakin ingin menghapus data produk ini?');
        return view('product.index', compact('products'));
    }

    public function store(Request $request)
    {
        $id = $request->id;
        
        // Sanitize Currency Inputs
        $request->merge([
            'harga_jual' => $request->harga_jual ? str_replace('.', '', $request->harga_jual) : 0,
            'harga_jual_besar' => $request->harga_jual_besar ? str_replace('.', '', $request->harga_jual_besar) : 0,
            'harga_beli' => $request->harga_beli ? str_replace('.', '', $request->harga_beli) : 0,
        ]);

        $rules = [
            'nama_produk' => [
                'required',
                \Illuminate\Validation\Rule::unique('products')->ignore($id)->whereNull('deleted_at')
            ],
            'harga_jual' => 'required|numeric|min:0',
            'harga_jual_besar' => 'nullable|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'stok_min' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
        ];

        // Stok is automatic 0 for new, ignored for update
        
        $request->validate($rules,
            [
                'nama_produk.required' => 'Nama produk wajib diisi.',
                'nama_produk.unique' => 'Nama produk sudah ada.',
                'harga_jual.required' => 'Harga jual wajib diisi.',
                'harga_jual.numeric' => 'Harga jual harus berupa angka.',
                'harga_jual.min' => 'Harga jual minimal 0.',
                'harga_beli.required' => 'Harga beli wajib diisi.',
                'harga_beli.numeric' => 'Harga beli harus berupa angka.',
                'harga_beli.min' => 'Harga beli minimal 0.',
                'stok_min.required' => 'Stok minimal wajib diisi.',
                'stok_min.numeric' => 'Stok minimal harus berupa angka.',
                'stok_min.min' => 'Stok minimal minimal 0.',
                'unit_id.required' => 'Satuan wajib diisi.',
                'unit_id.exists' => 'Satuan tidak valid.',
            ]
        );

        $unit = Unit::find($request->unit_id);
        $isi = $unit ? $unit->isi : 1;

        // Custom Validation: Harga Beli < Harga Jual
        if ($request->harga_beli > $request->harga_jual) {
            return back()->withErrors(['harga_beli' => 'Harga Beli harus lebih kecil dari Harga Jual (Satuan Kecil)!'])->withInput();
        }

        // Validate Big Unit Price if exists
        if ($request->harga_jual_besar > 0) {
            $modalBesar = $request->harga_beli * $isi;
            if ($modalBesar > $request->harga_jual_besar) {
                return back()->withErrors(['harga_jual_besar' => "Harga Jual Besar harus lebih besar atau sama dengan modal per bal (Rp " . number_format($modalBesar) . ")!"])->withInput();
            }
        }

        $newRequest =
            [
                'id' => $id,
                'nama_produk' => $request->nama_produk,
                'unit_id' => $request->unit_id,
                'harga_jual' => $request->harga_jual,
                'harga_jual_besar' => $request->harga_jual_besar,
                'harga_beli' => $request->harga_beli,
                'stok' => $request->stok ?? 0, // Allow updating stock directly
                'stok_gudang' => ($request->stok_gudang ?? 0) * $isi,
                'stok_min' => $request->stok_min,
                'is_active' => $request->is_active ? true : false,
            ];

        if (!$id) {
            $newRequest['sku'] = Product::nomorSku();
        }

        Product::updateOrCreate(
            ['id' => $id],
            $newRequest
        );
        toast()->success('Data produk berhasil disimpan.');
        return redirect()->route('master-data.product.index')->with('success', 'Product created successfully.');
    }
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        toast()->success('Data produk berhasil dihapus.');
        return redirect()->route('master-data.product.index');
    }
}
