<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $query = Transaction::where('status', 'paid');
        $this->applyFilters($query, $request);

        $totalSales = $query->sum('total');
        $totalTransactions = $query->count();
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Get total items sold
        // Clone query to avoid modifying the original query for subsequent usage if needed, 
        // but here we just need IDs. Re-running logic is safer or cloning.
        $transactionIds = $query->pluck('id');
        $totalItems = TransactionItem::whereIn('transaction_id', $transactionIds)->sum('quantity');

        // Top products
        $topProducts = TransactionItem::whereIn('transaction_id', $transactionIds)
            ->select('product_id', DB::raw('SUM(quantity) as quantity'), DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->limit(10)
            ->with('product:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'revenue' => $item->revenue
                ];
            });

        // Chart data
        $period = $request->get('period', 'today');
        $labels = [];
        $values = [];

        if ($period === 'today') {
            for ($i = 8; $i <= 22; $i += 2) {
                $labels[] = sprintf('%02d:00', $i);
                $values[] = Transaction::where('status', 'paid')
                    ->whereDate('created_at', today())
                    ->whereRaw('HOUR(created_at) >= ? AND HOUR(created_at) < ?', [$i, $i + 2])
                    ->sum('total');
            }
        } else {
            // For week, month, custom: show daily totals
            // Determine date range for chart
            $chartQuery = Transaction::where('status', 'paid');
            $this->applyFilters($chartQuery, $request);

            $dailySales = $chartQuery->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total')
            )
                ->groupBy('date')
                ->get()
                ->pluck('total', 'date');

            if ($period === 'week') {
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
            } elseif ($period === 'month') {
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
            } else { // custom
                $start = \Carbon\Carbon::parse($request->get('start_date', now()));
                $end = \Carbon\Carbon::parse($request->get('end_date', now()));
            }

            // Fill gaps
            // Cap at 31 days to prevent massive loops if user selects huge range
            $days = $start->diffInDays($end->copy()->addDay());
            if ($days > 31)
                $days = 31;

            for ($i = 0; $i < $days; $i++) {
                $date = $start->copy()->addDays($i)->format('Y-m-d');
                $labels[] = date('d/m', strtotime($date));
                $values[] = $dailySales[$date] ?? 0;
            }
        }

        return response()->json([
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'avg_transaction' => round($avgTransaction),
            'total_items' => $totalItems,
            'top_products' => $topProducts,
            'chart_data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        // Clear output buffer to avoid 0-byte PDF issues
        if (ob_get_length())
            ob_end_clean();

        $data = $this->getSalesData($request);

        $pdf = Pdf::loadView('reports.sales', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif'
        ]);

        return $pdf->download('laporan-penjualan.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Transaction::where('status', 'paid')->with('items.product');
        $this->applyFilters($query, $request);
        $transactions = $query->get();

        $csv = "Invoice,Tanggal,Total,Item\n";
        foreach ($transactions as $tx) {
            $items = $tx->items->pluck('product.name')->implode('; ');
            // Escape double quotes in items
            $items = str_replace('"', '""', $items);
            $csv .= "{$tx->invoice_number},{$tx->created_at},{$tx->total},\"{$items}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-penjualan.csv"'
        ]);
    }

    private function getSalesData(Request $request)
    {
        $query = Transaction::where('status', 'paid')->with('items.product');
        $this->applyFilters($query, $request);

        return [
            'period' => $request->get('period', 'today'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'generated_at' => now()->format('d M Y H:i'),
            'transactions' => $query->get(),
            'total_sales' => $query->sum('total'), // Re-calculating might be expensive but simplest for PDF data consistency
            'total_transactions' => $query->count()
        ];
    }

    private function applyFilters($query, Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                break;
            case 'custom':
                if ($startDate && $endDate) {
                    // Ensure end date includes the whole day
                    $query->whereBetween('created_at', [
                        $startDate,
                        \Carbon\Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
        }
    }
}
