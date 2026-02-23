<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class ReceivableController extends Controller
{
    // List Customers with Debt
    public function index()
    {
        // Get customers who have transactions with remaining_debt > 0
        $customers = Customer::whereHas('transactions', function($q) {
            $q->where('remaining_debt', '>', 0);
        })->withSum(['transactions' => function($q) {
            $q->where('remaining_debt', '>', 0);
        }], 'remaining_debt')
        ->get();

        return view('receivable.index', compact('customers'));
    }

    // List Transactions for a specific Customer
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        
        $transactions = Transaction::with('payments')
            ->where('customer_id', $id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();
            
        return view('receivable.show', compact('customer', 'transactions'));
    }

    // Store Payment (Cicilan)
    public function storePayment(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required', // will sanitize below
            'payment_date' => 'required|date',
            'note' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            
            $transaction = Transaction::findOrFail($request->transaction_id);
            
            // Sanitize Amount (Rp 100.000 -> 100000)
            $amount = str_replace('.', '', $request->amount);
            $amount = (float) $amount;
            
            if ($amount <= 0) {
                 return back()->with('error', 'Jumlah pembayaran tidak valid!');
            }

            if ($amount > $transaction->remaining_debt) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa hutang!');
            }
            
            // Create Payment Record
            TransactionPayment::create([
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'payment_date' => $request->payment_date,
                'note' => $request->note,
                'user_id' => auth()->id()
            ]);
            
            // Reduce Debt
            $transaction->decrement('remaining_debt', $amount);
            
            // Check if paid off
            if ($transaction->remaining_debt <= 0) {
                 $transaction->remaining_debt = 0; // Ensure no negative
                 $transaction->status = 'selesai';
                 $transaction->save();
            }
            
            DB::commit();
            
            return back()->with('success', 'Pembayaran berhasil disimpan.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    // List of payments for a transaction (AJAX)
    public function history($transactionId)
    {
        $payments = TransactionPayment::where('transaction_id', $transactionId)
                    ->with('user')
                    ->orderBy('payment_date', 'desc')
                    ->get();
                    
        return response()->json($payments);
    }

    // Print Payment History (Struk/Invoice Angsuran)
    public function printPaymentHistory($transactionId)
    {
        $transaction = Transaction::with(['customer', 'payments.user', 'details.product'])->findOrFail($transactionId);
        return view('receivable.print_history', compact('transaction'));
    }

    // Print ALL History for a Customer
    public function printCustomerFullHistory($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $transactions = Transaction::where('customer_id', $customerId)
                        ->orderBy('tanggal', 'desc')
                        ->orderBy('id', 'desc')
                        ->get();

        return view('receivable.print_customer_history', compact('customer', 'transactions'));
    }

    public function openingBalance()
    {
        $customers = Customer::all();
        
        // Generate Auto Code for Opening Balance (AR = Accounts Receivable)
        $maxId = Transaction::withTrashed()->max('id') ?? 0;
        $no_transaksi = 'ARX-' . date('Ymd') . '-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);

        return view('receivable.opening-balance', compact('customers', 'no_transaksi'));
    }

    public function storeOpeningBalance(Request $request)
    {
        // Strip currency formatting
        if ($request->has('total_piutang')) {
            $request->merge([
                'total_piutang' => str_replace('.', '', $request->total_piutang)
            ]);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'tanggal' => 'required|date',
            'total_piutang' => 'required|numeric|min:1',
            'no_transaksi' => 'required|unique:transactions,no_transaksi',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Opening Balance Validation Failed', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'customer_id' => $request->customer_id,
                'tanggal' => $request->tanggal,
                'no_transaksi' => $request->no_transaksi,
                'keterangan' => $request->keterangan ?? 'Saldo Awal Piutang',
                'total_harga' => $request->total_piutang,
                'bayar' => 0,
                'kembalian' => 0,
                'biaya_kirim' => 0,
                'biaya_tambahan' => 0,
                'remaining_debt' => $request->total_piutang,
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            toast()->success('Saldo awal piutang berhasil disimpan.');
            return redirect()->route('receivable.index');

        } catch (\Exception $e) {
            DB::rollback();
            \Illuminate\Support\Facades\Log::error('Opening Balance Exception: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan saldo awal: ' . $e->getMessage());
        }
    }

    public function printRawPayment($id)
    {
        // $id is transaction_id
        $transaction = \App\Models\Transaction::with(['customer', 'payments.user', 'details.product'])->findOrFail($id);

        try {
            // Browser Native Print: Return HTML View
            return view('receivable.print_payment_thermal', compact('transaction'));

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Gagal memuat struk: " . $e->getMessage()], 500);
        }
    }
}
