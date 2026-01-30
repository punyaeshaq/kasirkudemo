<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$activations = DB::table('activations')->get();

echo "=== ACTIVATIONS IN DB ===\n";
foreach ($activations as $a) {
    echo "ID: {$a->id} | MachineID: {$a->machine_id} | Activated: {$a->activated} | Expired: {$a->expired_at}\n";
}
echo "=========================\n";
