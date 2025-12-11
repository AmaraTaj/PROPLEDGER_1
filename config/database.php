<?php
/**
 * MySQL Database Configuration for PROPLEDGER
 * Shared configuration for both PHP backend and Next.js MySQL connection
 */

// Database credentials
$dbHost = 'localhost';
$dbPort = 3306;
$dbName = 'propledger_db';
$dbUser = 'root';
$dbPass = ''; // Default XAMPP has no password for root

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Log error and return JSON for API endpoints
    error_log("Database connection failed: " . $e->getMessage());
    
    // If called via HTTP request, return JSON error
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]);
        exit;
    }
    
    throw $e;
}

// Export connection for use in other files
// $pdo is now available in any file that includes this one
?>
