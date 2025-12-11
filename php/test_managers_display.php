<?php
// Test if agents are appearing in managers module
require_once '../config/database.php';

echo "=== TESTING PORTFOLIO MANAGERS DISPLAY ===\n\n";

// Test the exact query used by get_agents.php
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

echo "Agents that should appear in Portfolio Managers:\n";
echo "Found " . count($agents) . " agents:\n\n";

foreach ($agents as $agent) {
    echo "Name: {$agent['full_name']}\n";
    echo "Specialization: {$agent['specialization']}\n";
    echo "City: {$agent['city']}\n";
    echo "Status: {$agent['status']}\n";
    echo "License: {$agent['license_number']}\n";
    echo "Experience: {$agent['experience']}\n";
    echo "Created: {$agent['created_at']}\n";
    echo "---\n";
}

// Test the API endpoint directly
echo "\n=== TESTING API ENDPOINT ===\n";
$json_response = json_encode([
    'success' => true,
    'agents' => $agents,
    'count' => count($agents)
]);

echo "API Response Preview:\n";
echo substr($json_response, 0, 200) . "...\n";

echo "\nIf agents are not showing in the browser:\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Verify get_agents.php is accessible\n";
echo "3. Check if loadRegisteredAgents() function is being called\n";
?>
