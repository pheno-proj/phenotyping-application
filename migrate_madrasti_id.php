<?php
require_once 'db_connect.php';
try {
    $pdo->exec("ALTER TABLE children ADD COLUMN madrasti_id VARCHAR(50) UNIQUE AFTER child_id");
    echo "Migration Success: Added madrasti_id to children table.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration already applied: madrasti_id exists.\n";
    } else {
        echo "Migration Error: " . $e->getMessage() . "\n";
    }
}
