<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $todaySales = Transaction::where('status', 'paid')
            ->whereDate('created_at', today())
            ->sum('total');

        $yesterdaySales = Transaction::where('status', 'paid')
            ->whereDate('created_at', today()->subDay())
            ->sum('total');

        $todayTransactions = Transaction::whereDate('created_at', today())->count();
        $yesterdayTransactions = Transaction::whereDate('created_at', today()->subDay())->count();

        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<', 10)->count();

        $salesChange = $yesterdaySales > 0
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
            : 0;

        $txChange = $yesterdayTransactions > 0
            ? round((($todayTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100, 1)
            : 0;

        // Recent transactions
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'invoice' => $tx->invoice_number,
                    'time' => $tx->created_at->format('H:i'),
                    'items' => $tx->items_count,
                    'total' => $tx->total,
                    'status' => $tx->status
                ];
            });

        // Top products today
        $topProducts = \App\Models\TransactionItem::whereHas('transaction', function ($q) {
            $q->where('status', 'paid')->whereDate('created_at', today());
        })
            ->select('product_id', \DB::raw('SUM(quantity) as sold'), \DB::raw('SUM(subtotal) as revenue'))
            ->groupBy('product_id')
            ->orderByDesc('sold')
            ->limit(5)
            ->with('product:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name ?? 'Unknown',
                    'sold' => $item->sold,
                    'revenue' => $item->revenue
                ];
            });

        return response()->json([
            'stats' => [
                'total_sales' => $todaySales,
                'sales_change' => $salesChange,
                'total_transactions' => $todayTransactions,
                'tx_change' => $txChange,
                'total_products' => $totalProducts,
                'low_stock' => $lowStockProducts
            ],
            'recent_transactions' => $recentTransactions,
            'top_products' => $topProducts
        ]);
    }
}
