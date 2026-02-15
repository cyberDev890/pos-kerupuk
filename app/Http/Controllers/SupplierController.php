<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::all();
        confirmDelete('Hapus Data', 'Yakin ingin menghapus data suplier ini?');
        return view('supplier.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        Supplier::create($request->all());
        toast()->success('Data suplier berhasil disimpan.');
        return redirect()->route('master-data.supplier.index');
    }

    public function update(Request $request, Supplier $supplier)
    {
         $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $supplier->update($request->all());
        toast()->success('Data suplier berhasil diperbarui.');
        return redirect()->route('master-data.supplier.index');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        toast()->success('Data suplier berhasil dihapus.');
        return redirect()->route('master-data.supplier.index');
    }
}
