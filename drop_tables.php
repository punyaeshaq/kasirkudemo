<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Schema::disableForeignKeyConstraints();
Schema::dropIfExists('debt_payments');
Schema::dropIfExists('customers');
Schema::enableForeignKeyConstraints();

echo "Tables dropped." . PHP_EOL;
