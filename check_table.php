<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=StoreApp;charset=utf8mb4', 'root', 'StoreApp2026!');
    $stmt = $pdo->query("SHOW TABLES LIKE 'personal_access_tokens'");
    $result = $stmt->fetchAll(PDO::FETCH_NUM);
    echo "Tables found: " . count($result) . "\n";
    foreach ($result as $row) {
        echo "  - " . $row[0] . "\n";
    }
    
    // Also check if migration was actually run
    $stmt2 = $pdo->query("SELECT * FROM migrations WHERE migration LIKE '%personal_access%'");
    $migrations = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "Migrations found: " . count($migrations) . "\n";
    foreach ($migrations as $m) {
        echo "  - " . json_encode($m) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
