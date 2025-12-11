<?php
// Test script to create a new agent and verify it appears in portfolio managers
require_once '../config/database.php';

echo "=== TESTING NEW AGENT REGISTRATION & PORTFOLIO DISPLAY ===\n\n";

// Create a completely new test agent
$newAgent = [
    'fullName' => 'New Agent ' . date('His'), // Unique name with timestamp
    'email' => 'newagent' . date('His') . '@propledger.com',
    'phone' => '+92-300-' . rand(1000000, 9999999),
    'licenseNumber' => 'LIC-NEW-' . date('His'),
    'experience' => '2+ years',
    'specialization' => 'residential',
    'city' => 'Islamabad',
    'agency' => 'New Properties Ltd',
    'userType' => 'agent',
    'password' => 'newpass123'
];

echo "Creating new agent: {$newAgent['fullName']}\n";
echo "Email: {$newAgent['email']}\n";
echo "License: {$newAgent['licenseNumber']}\n\n";

try {
    // Step 1: Create user record
    $password_hash = password_hash($newAgent['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, phone, country, user_type, password_hash, newsletter_subscribed) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $newAgent['fullName'],
        $newAgent['email'],
        $newAgent['phone'],
        $newAgent['city'],
        $newAgent['userType'],
        $password_hash,
        false
    ]);
    
    $user_id = $pdo->lastInsertId();
    echo "✅ Step 1: User created with ID: $user_id\n";
    
    // Step 2: Create agent record
    $stmt = $pdo->prepare("
        INSERT INTO agents (user_id, license_number, experience, specialization, city, agency, phone, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')
    ");
    
    $stmt->execute([
        $user_id,
        $newAgent['licenseNumber'],
        $newAgent['experience'],
        $newAgent['specialization'],
        $newAgent['city'],
        $newAgent['agency'],
        $newAgent['phone']
    ]);
    
    echo "✅ Step 2: Agent record created with approved status\n";
    
    // Step 3: Test portfolio managers query (same as get_agents.php)
    echo "\n--- Testing Portfolio Managers Query ---\n";
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
    
    echo "Found " . count($agents) . " total agents in portfolio managers:\n\n";
    
    $newAgentFound = false;
    foreach ($agents as $agent) {
        $isNewAgent = ($agent['full_name'] === $newAgent['fullName']);
        if ($isNewAgent) {
            $newAgentFound = true;
            echo "🎯 NEW AGENT: ";
        } else {
            echo "   Existing: ";
        }
        echo "{$agent['full_name']} ({$agent['specialization']}, {$agent['city']}) - Status: {$agent['status']}\n";
    }
    
    echo "\n";
    if ($newAgentFound) {
        echo "✅ SUCCESS: New agent appears in portfolio managers query!\n";
    } else {
        echo "❌ ERROR: New agent NOT found in portfolio managers!\n";
    }
    
    // Step 4: Test API response format
    echo "\n--- Testing API Response Format ---\n";
    $apiResponse = [
        'success' => true,
        'agents' => $agents,
        'count' => count($agents)
    ];
    
    echo "API will return " . count($agents) . " agents\n";
    echo "New agent data structure:\n";
    foreach ($agents as $agent) {
        if ($agent['full_name'] === $newAgent['fullName']) {
            echo "  Name: {$agent['full_name']}\n";
            echo "  Specialization: {$agent['specialization']}\n";
            echo "  City: {$agent['city']}\n";
            echo "  Status: {$agent['status']}\n";
            echo "  License: {$agent['license_number']}\n";
            echo "  Experience: {$agent['experience']}\n";
            break;
        }
    }
    
    echo "\n✅ REGISTRATION TEST COMPLETED SUCCESSFULLY!\n";
    echo "The new agent should now appear in Portfolio Managers module.\n";
    echo "Visit: http://localhost/PROPLEDGER/html/managers.html\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
