<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReturn;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // 1. Transactions (Gross Sales)
        $transactions = Transaction::with(['details.product.unit', 'customer'])
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->get();

        $totalRevenue = 0;
        $totalCOGS = 0;

        foreach ($transactions as $trx) {
            // Net Revenue for this transaction (Pure Product Sales)
            // Excluding Shipping & Extra Costs to reflect true "Omzet Barang"
            $trxRevenue = $trx->total_harga - $trx->biaya_kirim - $trx->biaya_tambahan;
            $totalRevenue += $trxRevenue;

            foreach ($trx->details as $detail) {
                $totalCOGS += $this->calculateTransactionDetailCOGS($detail);
            }
        }

        // 2. Returns (Deductions)
        $returns = ProductReturn::with(['details.product', 'details.unit', 'transaction.details'])
            ->where('jenis_retur', 'penjualan') // Only Sales Returns affect Sales Profit
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->get();

        $totalReturnRevenue = 0;
        $totalReturnCOGS = 0;

        foreach ($returns as $ret) {
            $totalReturnRevenue += $ret->total_harga; // Amount refunded

            foreach ($ret->details as $detail) {
                $totalReturnCOGS += $this->calculateReturnDetailCOGS($detail);
            }
        }

        $grossProfit = $totalRevenue - $totalCOGS;
        $netReturnImpact = $totalReturnRevenue - $totalReturnCOGS; // Lost Profit
        $netProfit = $grossProfit - $netReturnImpact;

        return view('report.profit_loss', compact(
            'startDate', 'endDate', 
            'transactions', 'returns',
            'totalRevenue', 'totalCOGS', 
            'totalReturnRevenue', 'totalReturnCOGS',
            'grossProfit', 'netProfit'
        ));
    }

    private function calculateTransactionDetailCOGS($detail)
    {
        if (!$detail->product) return 0;
        
        $product = $detail->product;
        $qtyInPcs = $detail->jumlah;
        $unit = $product->unit;

        // Heuristic: Check if sold as "Besar"
        // If Unit exists and has content > 1
        if ($unit && $unit->isi > 1) {
             // 1. Calculate Expected Prices
             $priceSmall = $product->harga_jual;
             $priceBesar = $product->harga_jual_besar ?? ($priceSmall * $unit->isi);
             
             // 2. Determine Unit Type based on Deal Price
             // If Deal Price is significantly closer to Big Price than Small Price
             // Or if Deal Price is > (Small Price * Isi * 0.5) -- a safe threshold for "Bulk" vs "Single"
             
             // Logic: 
             // Small Price ~ 10.000
             // Big Price ~ 100.000
             // Custom Price ~ 90.000
             // If price > 50.000 (midpoint), assume Big.
             
             $threshold = ($priceBesar + $priceSmall) / 2;
             
             // If Deal Price is above threshold, treat as Big Unit
             // But also handle case where Big Price might be null/zero
             if ($priceBesar > 0 && $detail->harga_satuan >= $threshold) {
                  $qtyInPcs = $detail->jumlah * $unit->isi;
             }
             // Fallback: If absolute difference to Big Price is within reasonable margin (e.g., 20%)
             elseif ($priceBesar > 0 && abs($detail->harga_satuan - $priceBesar) < ($priceBesar * 0.2)) {
                  $qtyInPcs = $detail->jumlah * $unit->isi;
             }
        }
        
        // COGS = Qty(Pcs) * Product Buy Price (Per Pcs)
        return $qtyInPcs * $product->harga_beli;
    }

    private function calculateReturnDetailCOGS($detail)
    {
         if (!$detail->product) return 0;

         // Use stored conversion if available (from recent migration)
         $conversion = $detail->conversion ?? 1;
         
         // Fallback logic if conversion is missing (for old data)
         if ($conversion <= 1 && $detail->product->unit && $detail->product->unit->isi > 1) {
             $product = $detail->product;
             $unit = $product->unit;
             $priceSmall = $product->harga_jual;
             $priceBesar = $product->harga_jual_besar ?? ($priceSmall * $unit->isi);
             
             // Same heuristic as sales
             $threshold = ($priceBesar + $priceSmall) / 2;
             
             // Note: return_details table usually stores 'harga_satuan' as well.
             if ($priceBesar > 0 && $detail->harga_satuan >= $threshold) {
                  $conversion = $unit->isi;
             }
         }

         $qtyInPcs = $detail->jumlah * $conversion;

         // COGS = Qty(Pcs) * Product Buy Price
         return $qtyInPcs * $detail->product->harga_beli;
    }
}
