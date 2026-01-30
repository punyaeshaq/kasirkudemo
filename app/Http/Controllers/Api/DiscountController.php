<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::latest()->get();
        return response()->json(['data' => $discounts]);
    }

    public function active()
    {
        $discounts = Discount::active()->get();
        return response()->json(['data' => $discounts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_purchase' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $discount = Discount::create($validated);

        return response()->json([
            'message' => 'Diskon berhasil dibuat',
            'data' => $discount
        ], 201);
    }

    public function show(Discount $discount)
    {
        return response()->json(['data' => $discount]);
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_purchase' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $discount->update($validated);

        return response()->json([
            'message' => 'Diskon berhasil diperbarui',
            'data' => $discount
        ]);
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return response()->json(['message' => 'Diskon berhasil dihapus']);
    }
}
