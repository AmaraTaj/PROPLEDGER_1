<?php
// Debug script to check agents API
require_once '../config/database.php';

echo "=== DEBUGGING AGENTS API ===\n\n";

// Check total agents in database
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM agents a JOIN users u ON a.user_id = u.id WHERE a.status IN ('approved', 'pending')");
$stmt->execute();
$result = $stmt->fetch();
echo "Total agents in database: " . $result['count'] . "\n\n";

// Test the exact query from get_agents.php
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

echo "Agents found: " . count($agents) . "\n";
foreach ($agents as $agent) {
    echo "- {$agent['full_name']} ({$agent['city']}, {$agent['status']})\n";
}

// Test API response format
$response = [
    'success' => true,
    'agents' => $agents,
    'count' => count($agents)
];

echo "\nAPI Response:\n";
echo json_encode($response, JSON_PRETTY_PRINT);
?>
