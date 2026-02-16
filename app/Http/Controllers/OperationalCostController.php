<?php

namespace App\Http\Controllers;

use App\Models\OperationalCost;
use Illuminate\Http\Request;

class OperationalCostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $operationalCosts = OperationalCost::latest()->paginate(10);
        confirmDelete('Hapus Data', 'Yakin ingin menghapus biaya operasional ini?');
        return view('budget.operational.index', compact('operationalCosts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->merge([
            'amount' => str_replace('.', '', $request->amount)
        ]);

        $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        OperationalCost::create($request->all());

        toast('Biaya operasional berhasil ditambahkan', 'success');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(OperationalCost $operational)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OperationalCost $operational)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OperationalCost $operational)
    {
        //
        $request->merge([
            'amount' => str_replace('.', '', $request->amount)
        ]);

        $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $operational->update($request->all());

        toast('Biaya operasional berhasil diperbarui', 'success');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OperationalCost $operational)
    {
        //
        $operational->delete();
        toast('Biaya operasional berhasil dihapus', 'success');
        return back();
    }
}
