<?php
/**
 * Test script to check users table columns
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // Get columns from users table
    $columns = $db->query("DESC users")->fetchAll();
    
    echo "<h2>Users Table Columns</h2>";
    echo "<pre>";
    foreach ($columns as $col) {
        printf("%-25s | %-20s | %s\n", $col['Field'], $col['Type'], $col['Null'] . ' ' . ($col['Default'] ?? ''));
    }
    echo "</pre>";
    
    // Check for specific columns we need
    $columnNames = array_map(fn($col) => $col['Field'], $columns);
    
    echo "<h2>Required Columns Check</h2>";
    $requiredColumns = [
        'id', 'prefix', 'first_name', 'last_name', 'member_number', 'national_id',
        'email', 'phone', 'position', 'academic_rank', 'school_organization',
        'work_phone', 'education_area', 'region', 'birth_date', 'home_address',
        'work_address', 'member_type', 'status', 'role', 'password'
    ];
    
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columnNames) ? '✅' : '❌';
        echo "$exists $col\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
