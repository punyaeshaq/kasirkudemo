<?php
$host = 'db.itvuoknqvhjrgopdozpa.supabase.co';
$port = '5432';
$db = 'postgres';
$user = 'postgres';
$pass = 'rikikurni123';

echo "Testing connection to:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "User: $user\n";
echo "--------------------------\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "[SUCCESS] Connection successful!\n";
} catch (PDOException $e) {
    echo "[ERROR] Connection failed: " . $e->getMessage() . "\n";

    // Check DNS
    echo "\nDNS Check:\n";
    $ip = gethostbyname($host);
    echo "Resolved IP: $ip\n";
    if ($ip == $host) {
        echo "WARNING: Could not resolve hostname to an IP address.\n";
    }
}
