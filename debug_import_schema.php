<?php
require_once 'db_connect.php';
$tables = ['children', 'parent', 'behavioral_history', 'academic_grades', 'alert'];
foreach($tables as $t) {
    echo "--- $t ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $t");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error or table $t does not exist: " . $e->getMessage() . "\n";
    }
}
