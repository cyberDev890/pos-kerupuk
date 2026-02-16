<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $salaries = Salary::latest()->paginate(10);
        confirmDelete('Hapus Data', 'Yakin ingin menghapus data gaji ini?');
        return view('budget.salary.index', compact('salaries'));
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
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $currency = str_replace('.', '', $request->amount);
        $request->merge(['amount' => $currency]);
        
        Salary::create($request->all());

        toast('Data gaji berhasil ditambahkan', 'success');
        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Salary $salary)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Salary $salary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Salary $salary)
    {
        //
        $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $currency = str_replace('.', '', $request->amount);
        $request->merge(['amount' => $currency]);

        $salary->update($request->all());

        toast('Data gaji berhasil diperbarui', 'success');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Salary $salary)
    {
        //
        $salary->delete();
        toast('Data gaji berhasil dihapus', 'success');
        return back();
    }
}
