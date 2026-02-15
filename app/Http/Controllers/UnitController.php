<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        confirmDelete('Hapus Data', 'Yakin ingin menghapus data satuan ini?');
        return view('unit.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:255',
            'satuan_kecil' => 'required|string|max:255',
            'satuan_besar' => 'required|string|max:255',
            'isi' => 'required|integer|min:1',
        ]);

        Unit::create($request->all());
        toast()->success('Data satuan berhasil disimpan.');
        return redirect()->route('master-data.unit.index');
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'nama_satuan' => 'required|string|max:255',
            'satuan_kecil' => 'required|string|max:255',
            'satuan_besar' => 'required|string|max:255',
            'isi' => 'required|integer|min:1',
        ]);

        $unit->update($request->all());
        toast()->success('Data satuan berhasil diperbarui.');
        return redirect()->route('master-data.unit.index');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        toast()->success('Data satuan berhasil dihapus.');
        return redirect()->route('master-data.unit.index');
    }
}
