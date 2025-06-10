<?php
include 'db_connect.php';

function checkTableStructure($conn) {
    // Check if users table exists and its structure
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        echo "Users table does not exist!\n";
        return false;
    }

    $result = $conn->query("DESCRIBE users");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = $row['Type'];
    }

    echo "Current users table structure:\n";
    print_r($columns);

    // Check for required columns
    $required_columns = [
        'id' => 'int',
        'registration_number' => 'varchar',
        'fullname' => 'varchar',
        'password_hash' => 'varchar'
    ];

    foreach ($required_columns as $column => $type) {
        if (!isset($columns[$column])) {
            echo "Missing required column: $column\n";
            return false;
        }
    }

    return true;
}

// Check database structure
$structure_ok = checkTableStructure($conn);
if (!$structure_ok) {
    echo "Database structure needs to be updated.\n";
} else {
    echo "Database structure is correct.\n";
    
    // Check for any existing users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "Number of existing users: " . $row['count'] . "\n";
}
?> 