<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, try to connect to MySQL server
$conn = new mysqli('localhost', 'root', '');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL server successfully.<br>";

// Create database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS university_voting_system");
$conn->select_db("university_voting_system");
echo "Database selected/created.<br>";

// Read and execute the SQL file
$sql_file = file_get_contents('student/VoterRegistrationDB.sql');
if ($sql_file === false) {
    die("Could not read SQL file.<br>");
}

// Split SQL file into individual queries
$queries = array_filter(array_map('trim', explode(';', $sql_file)));

// Execute each query
$success = true;
foreach ($queries as $query) {
    if (empty($query)) continue;
    
    // Skip DROP DATABASE and CREATE DATABASE statements as we've already handled them
    if (stripos($query, 'DROP DATABASE') !== false || stripos($query, 'CREATE DATABASE') !== false) {
        continue;
    }
    
    if ($conn->query($query) === false) {
        echo "Error executing query: " . $conn->error . "<br>";
        echo "Query was: " . htmlspecialchars($query) . "<br><br>";
        $success = false;
    }
}

if ($success) {
    echo "Database structure updated successfully.<br>";
    
    // Now check if we need to update any existing passwords
    $result = $conn->query("SELECT id, password_hash FROM users");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $password_hash = $row['password_hash'];
            // Check if it's a base64 encoded password
            if (base64_decode($password_hash, true) !== false) {
                // It's likely a base64 encoded password, let's update it
                $decoded = base64_decode($password_hash);
                $new_hash = password_hash($decoded, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hash, $row['id']);
                $stmt->execute();
                echo "Updated password hash for user ID: " . $row['id'] . "<br>";
            }
        }
    }
    
    echo "Password updates completed.<br>";
    
    // Verify the users table structure
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "<br>Current users table structure:<br>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
    }
    
    // Show number of users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "<br>Number of users in database: " . $row['count'];
}

$conn->close();
?> 