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
        $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $currency = str_replace('.', '', $request->amount);
        $request->merge(['amount' => $currency]);

        OperationalCost::create($request->all());

        toast('Biaya operasional berhasil ditambahkan', 'success');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(OperationalCost $operationalCost)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OperationalCost $operationalCost)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OperationalCost $operationalCost)
    {
        //
        $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        $currency = str_replace('.', '', $request->amount);
        $request->merge(['amount' => $currency]);

        $operationalCost->update($request->all());

        toast('Biaya operasional berhasil diperbarui', 'success');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OperationalCost $operationalCost)
    {
        //
        $operationalCost->delete();
        toast('Biaya operasional berhasil dihapus', 'success');
        return back();
    }
}
