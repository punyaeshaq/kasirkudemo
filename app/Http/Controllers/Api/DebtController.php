<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DebtPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    /**
     * Display a listing of incomplete transactions (debts).
     */
    public function index()
    {
        $debts = Transaction::with('customer')
            ->where('remaining_debt', '>', 0)
            ->latest()
            ->paginate(10);

        return response()->json($debts);
    }

    /**
     * Store a new debt payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'note' => 'nullable|string'
        ]);

        $transaction = Transaction::findOrFail($request->transaction_id);

        if ($request->amount > $transaction->remaining_debt) {
            return response()->json(['message' => 'Payment amount exceeds remaining debt.'], 422);
        }

        $payment = DebtPayment::create([
            'transaction_id' => $transaction->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'note' => $request->note,
            'paid_at' => now(),
        ]);

        $transaction->decrement('remaining_debt', $request->amount);

        if ($transaction->remaining_debt <= 0) {
            $transaction->update(['status' => 'paid']);
        }

        return response()->json($payment, 201);
    }

    /**
     * Get history of debt payments for a transaction.
     */
    public function show($transactionId)
    {
        $payments = DebtPayment::where('transaction_id', $transactionId)->latest()->get();
        return response()->json($payments);
    }
}
