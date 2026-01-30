<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Customers Table: " . (Schema::hasTable('customers') ? 'YES' : 'NO') . PHP_EOL;
echo "DebtPayments Table: " . (Schema::hasTable('debt_payments') ? 'YES' : 'NO') . PHP_EOL;
echo "Transaction.customer_id: " . (Schema::hasColumn('transactions', 'customer_id') ? 'YES' : 'NO') . PHP_EOL;
