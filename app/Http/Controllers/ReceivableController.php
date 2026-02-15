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
        
        $transactions = Transaction::where('customer_id', $id)
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
}
