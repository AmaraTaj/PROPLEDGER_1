<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

try {
    // Count total properties (from all categories)
    $propertyQuery = "SELECT COUNT(*) as total FROM properties";
    $propertyStmt = $pdo->prepare($propertyQuery);
    $propertyStmt->execute();
    $propertyResult = $propertyStmt->fetch(PDO::FETCH_ASSOC);
    $totalProperties = $propertyResult['total'] ?? 0;

    // Count total registered users (excluding admins)
    $userQuery = "SELECT COUNT(*) as total FROM users WHERE user_type != 'admin'";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute();
    $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $userResult['total'] ?? 0;

    // Calculate total investments (sum of all property values)
    $investmentQuery = "SELECT SUM(price) as total FROM properties";
    $investmentStmt = $pdo->prepare($investmentQuery);
    $investmentStmt->execute();
    $investmentResult = $investmentStmt->fetch(PDO::FETCH_ASSOC);
    $totalInvestments = $investmentResult['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'stats' => [
            'totalProperties' => (int)$totalProperties,
            'totalUsers' => (int)$totalUsers,
            'totalInvestments' => (float)$totalInvestments,
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch stats',
        'error' => $e->getMessage()
    ]);
}
?>
