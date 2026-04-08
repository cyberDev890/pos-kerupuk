<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayableController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::whereHas('purchases', function($q) {
            $q->where('remaining_debt', '>', 0);
        })->withSum(['purchases' => function($q) {
            $q->where('remaining_debt', '>', 0); // only unpaid purchases are summed for the main listing
        }], 'remaining_debt')
        ->get();
            
        return view('payable.index', compact('suppliers'));
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $purchases = Purchase::with(['supplier', 'payments.user'])
            ->where('supplier_id', $id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();
            
        return view('payable.show', compact('supplier', 'purchases'));
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $purchase = Purchase::findOrFail($request->purchase_id);
            
            if ($request->amount > $purchase->remaining_debt) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa hutang!');
            }

            PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
                'user_id' => auth()->id(),
            ]);

            $purchase->increment('bayar', $request->amount);
            $purchase->decrement('remaining_debt', $request->amount);

            if ($purchase->remaining_debt <= 0) {
                $purchase->update(['status' => 'selesai']);
            }

            DB::commit();
            toast()->success('Pembayaran hutang berhasil dicatat.');
            return redirect()->route('payable.index');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }

    public function history($purchaseId)
    {
        $payments = PurchasePayment::with('user')
            ->where('purchase_id', $purchaseId)
            ->orderBy('payment_date', 'desc')
            ->get();
            
        return response()->json($payments);
    }

    public function destroyPayment($id)
    {
        try {
            DB::beginTransaction();

            $payment = PurchasePayment::findOrFail($id);
            $purchase = $payment->purchase;

            // Revert Debt and Paid Amount
            $purchase->decrement('bayar', $payment->amount);
            $purchase->increment('remaining_debt', $payment->amount);

            // Revert status if it was completed
            if ($purchase->status == 'selesai') {
                $purchase->update(['status' => 'pending']);
            }
            $purchase->save();

            // Delete Payment Record
            $payment->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pembayaran hutang berhasil dibatalkan.']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage()], 500);
        }
    }

    public function printPaymentHistory($purchaseId)
    {
        $purchase = Purchase::with(['supplier', 'payments.user', 'details.product'])->findOrFail($purchaseId);
        return view('payable.print_history', compact('purchase'));
    }

    public function printSupplierFullHistory($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $purchases = Purchase::where('supplier_id', $supplierId)
                        ->orderBy('tanggal', 'desc')
                        ->orderBy('id', 'desc')
                        ->get();

        return view('payable.print_supplier_history', compact('supplier', 'purchases'));
    }
}
