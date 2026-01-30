<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PiutangTest extends TestCase
{
    use DatabaseTransactions; // safe rollback

    public function test_can_create_customer()
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/customers', [
            'name' => 'John Doe Test',
            'phone' => '08123456789',
            'address' => 'Jl. Test'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('customers', ['name' => 'John Doe Test']);
    }

    public function test_can_create_debt_transaction()
    {
        $user = User::first() ?? User::factory()->create();
        $customer = Customer::create(['name' => 'Jane Doe Test']);

        // Ensure a product exists
        $product = Product::first();
        if (!$product) {
            $product = Product::create([
                'name' => 'Test Product',
                'price' => 10000,
                'stock' => 100,
                'category_id' => 1 // Assuming category 1 exists or nullable, usually required
            ]);
        }

        $response = $this->actingAs($user)->postJson('/api/transactions', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'price' => 10000]
            ],
            'subtotal' => 10000,
            'tax' => 0,
            'total' => 10000,
            'payment_method' => 'cash',
            'cash_received' => 5000, // Partial payment
            'customer_id' => $customer->id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'customer_id' => $customer->id,
            'remaining_debt' => 5000,
            'status' => 'pending'
        ]);
    }

    public function test_can_pay_debt()
    {
        $user = User::first() ?? User::factory()->create();
        $customer = Customer::create(['name' => 'Debtor Test']);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-' . rand(1000, 9999),
            'subtotal' => 10000,
            'total' => 10000,
            'remaining_debt' => 5000,
            'status' => 'pending',
            'payment_method' => 'cash'
        ]);

        $response = $this->actingAs($user)->postJson('/api/debts/pay', [
            'transaction_id' => $transaction->id,
            'amount' => 5000,
            'payment_method' => 'cash',
            'note' => 'Paid off'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'remaining_debt' => 0,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('debt_payments', [
            'transaction_id' => $transaction->id,
            'amount' => 5000
        ]);
    }
}
