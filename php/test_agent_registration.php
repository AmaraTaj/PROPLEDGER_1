<?php
// Test script to create a sample agent and verify it appears in managers
require_once '../config/database.php';

try {
    // Use the $pdo connection from database.php
    
    // First, create a test user
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, user_type) VALUES (?, ?, ?, ?)");
    $testEmail = 'test.agent@propledger.com';
    $hashedPassword = password_hash('testpass123', PASSWORD_DEFAULT);
    
    // Check if test user already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$testEmail]);
    $existingUser = $checkStmt->fetch();
    
    if ($existingUser) {
        $userId = $existingUser['id'];
        echo "Test user already exists with ID: $userId\n";
    } else {
        $stmt->execute(['Test Agent', $testEmail, $hashedPassword, 'agent']);
        $userId = $pdo->lastInsertId();
        echo "Created test user with ID: $userId\n";
    }
    
    // Check if agent record already exists
    $checkAgentStmt = $pdo->prepare("SELECT id FROM agents WHERE user_id = ?");
    $checkAgentStmt->execute([$userId]);
    $existingAgent = $checkAgentStmt->fetch();
    
    if (!$existingAgent) {
        // Create agent record
        $agentStmt = $pdo->prepare("
            INSERT INTO agents (user_id, license_number, experience, specialization, city, agency, phone, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $agentStmt->execute([
            $userId,
            'LIC-TEST-001',
            '5+ years',
            'residential',
            'Karachi',
            'Test Realty',
            '+92-300-1234567',
            'approved'
        ]);
        
        echo "Created test agent record\n";
    } else {
        echo "Test agent record already exists\n";
    }
    
    // Now test the get_agents.php endpoint
    echo "\n--- Testing get_agents.php endpoint ---\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.full_name,
            u.email,
            a.license_number,
            a.experience,
            a.specialization,
            a.city,
            a.agency,
            a.phone,
            a.status,
            a.commission_rate,
            a.total_sales,
            a.rating,
            a.created_at
        FROM agents a
        JOIN users u ON a.user_id = u.id
        WHERE a.status IN ('approved', 'pending')
        ORDER BY a.status DESC, a.created_at DESC
    ");
    
    $stmt->execute();
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($agents) . " agents:\n";
    foreach ($agents as $agent) {
        echo "- {$agent['full_name']} ({$agent['specialization']}, {$agent['city']}) - Status: {$agent['status']}\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
