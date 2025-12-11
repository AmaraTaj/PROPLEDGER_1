<?php
// Test database connection and check for users
header('Content-Type: text/plain');

echo "=== PROPLEDGER Database Diagnostic ===\n\n";

// Test 1: Check if database exists
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $result = $pdo->query("SHOW DATABASES LIKE 'propledger_db'");
    if ($result->rowCount() > 0) {
        echo "✓ Database 'propledger_db' exists\n";
    } else {
        echo "✗ Database 'propledger_db' NOT FOUND!\n";
        echo "  → Run: php/setup_database.php to create it\n";
        exit;
    }
} catch(Exception $e) {
    echo "✗ MySQL Connection Error: " . $e->getMessage() . "\n";
    echo "  → Make sure XAMPP MySQL is running\n";
    exit;
}

// Test 2: Connect to propledger_db
try {
    $pdo = new PDO('mysql:host=localhost;dbname=propledger_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to propledger_db\n\n";
} catch(Exception $e) {
    echo "✗ Database Connection Error: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Check if users table exists
try {
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "✓ Table 'users' exists\n";
    } else {
        echo "✗ Table 'users' NOT FOUND!\n";
        echo "  → Run: php/setup_database.php to create tables\n";
        exit;
    }
} catch(Exception $e) {
    echo "✗ Error checking tables: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Check users table structure
try {
    $result = $pdo->query("DESCRIBE users");
    echo "\n--- Users Table Structure ---\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch(Exception $e) {
    echo "✗ Error reading table structure: " . $e->getMessage() . "\n";
}

// Test 5: Count users
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    echo "\n✓ Total users in database: " . $count . "\n";
    
    if ($count == 0) {
        echo "\n⚠ WARNING: No users found!\n";
        echo "  → You need to create an account via signup page\n";
        echo "  → Or run a test user creation script\n";
    } else {
        // Show sample users
        echo "\n--- Sample Users ---\n";
        $stmt = $pdo->query("SELECT id, full_name, email, user_type, is_active FROM users LIMIT 5");
        while ($user = $stmt->fetch()) {
            echo "  ID: {$user['id']} | {$user['full_name']} | {$user['email']} | Type: {$user['user_type']} | Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
        }
    }
} catch(Exception $e) {
    echo "✗ Error counting users: " . $e->getMessage() . "\n";
}

// Test 6: Check user_sessions table
try {
    $result = $pdo->query("SHOW TABLES LIKE 'user_sessions'");
    if ($result->rowCount() > 0) {
        echo "\n✓ Table 'user_sessions' exists\n";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_sessions");
        $count = $stmt->fetch()['count'];
        echo "  Active sessions: " . $count . "\n";
    } else {
        echo "\n✗ Table 'user_sessions' NOT FOUND!\n";
    }
} catch(Exception $e) {
    echo "✗ Error checking sessions: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
?>
