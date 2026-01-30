<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'items', 'payments']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        $transactions = $query->latest()->paginate(20);

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qris,transfer',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        return DB::transaction(function () use ($request) {
            $cashReceived = $request->cash_received ?? $request->total;
            $remainingDebt = 0;
            $status = 'paid';
            $total = $request->total;

            if ($cashReceived < $total) {
                $remainingDebt = $total - $cashReceived;
                $status = 'pending';

                if (!$request->customer_id) {
                    throw new \Exception('Customer selection is required for debt/partial payments.', 422);
                }
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'customer_id' => $request->customer_id,
                'subtotal' => $request->subtotal,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'cash_received' => $cashReceived,
                'remaining_debt' => $remainingDebt,
                'change' => max(0, $cashReceived - $total),
                'status' => $status
            ]);

            // Create items and reduce stock
            foreach ($request->items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']
                ]);

                // Reduce stock
                $product = Product::find($item['product_id']);
                $product->decrementStock($item['quantity']);
            }

            // Create payment if any amount was paid
            if ($cashReceived > 0) {
                Payment::create([
                    'transaction_id' => $transaction->id,
                    'method' => $request->payment_method,
                    'amount' => min($cashReceived, $total), // Record mapped payment (capped at total if change given?)
                    // Actually, usually we record exactly what was paid or just the portion covering the bill?
                    // Standard POS: Payment amount = Total (if paid) or Partial Amount.
                    // If cash_received > total, payment amount = total, and change is recorded in transaction.
                    // If cash_received < total, payment amount = cash_received.
                    'reference' => $request->reference ?? null,
                    'status' => 'success'
                ]);
            }

            return response()->json([
                'data' => $transaction->load(['items.product', 'payments', 'customer']),
                'invoice_number' => $transaction->invoice_number
            ], 201);
        });
    }

    public function show(Transaction $transaction)
    {
        return response()->json([
            'data' => $transaction->load(['user', 'items.product', 'payments'])
        ]);
    }

    /**
     * Get transaction data for printing (public access, no auth required)
     */
    public function showForPrint($id)
    {
        $transaction = Transaction::with(['user', 'items.product', 'payments'])->find($id);

        if (!$transaction) {
            return response()->json(['error' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => $transaction
        ]);
    }

    public function generateQris(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        // Generate QRIS data (in real scenario, this would call payment gateway API)
        $qrisData = 'QRIS-KASIRKU-' . time() . '-' . $request->amount;

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5,
        ]);

        $qrcode = new QRCode($options);
        $qrImage = $qrcode->render($qrisData);

        return response()->json([
            'qr_image' => $qrImage,
            'qr_data' => $qrisData,
            'amount' => $request->amount
        ]);
    }

    public function qrisCallback(Request $request)
    {
        // Handle payment gateway callback
        $request->validate([
            'reference' => 'required|string',
            'status' => 'required|in:success,failed',
            'transaction_id' => 'required|exists:transactions,id'
        ]);

        $payment = Payment::where('reference', $request->reference)->first();

        if ($payment && $request->status === 'success') {
            $payment->markAsSuccess();
        }

        return response()->json(['message' => 'Callback received']);
    }

    public function destroy(Transaction $transaction)
    {
        return DB::transaction(function () use ($transaction) {
            // Restore stock for each item
            foreach ($transaction->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            // Delete related records
            $transaction->items()->delete();
            $transaction->payments()->delete();
            $transaction->debtPayments()->delete();

            // Delete the transaction
            $transaction->delete();

            return response()->json(['message' => 'Transaksi berhasil dihapus dan stok dikembalikan']);
        });
    }
}
