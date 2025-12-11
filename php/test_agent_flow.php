<?php
// Test complete agent registration and rating flow
require_once '../config/database.php';

echo "<h1>Testing Agent Registration and Rating Flow</h1>";

// Test 1: Check if agents exist
echo "<h2>1. Checking Agents in Database</h2>";
$stmt = $pdo->prepare("SELECT u.id, u.full_name, u.email, a.status FROM agents a JOIN users u ON a.user_id = u.id");
$stmt->execute();
$agents = $stmt->fetchAll();

echo "Found " . count($agents) . " agents:<br>";
foreach ($agents as $agent) {
    echo "- ID: {$agent['id']}, Name: {$agent['full_name']}, Status: {$agent['status']}<br>";
}

// Test 2: Check if users exist
echo "<h2>2. Checking Users in Database</h2>";
$stmt = $pdo->prepare("SELECT id, full_name, email, user_type FROM users WHERE user_type = 'investor'");
$stmt->execute();
$users = $stmt->fetchAll();

echo "Found " . count($users) . " investor users:<br>";
foreach ($users as $user) {
    echo "- ID: {$user['id']}, Name: {$user['full_name']}, Type: {$user['user_type']}<br>";
}

// Test 3: Check ratings table
echo "<h2>3. Checking Ratings Table</h2>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM agent_ratings");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Ratings table exists with {$result['count']} ratings<br>";
} catch (Exception $e) {
    echo "Ratings table error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Test Authentication and Rating Button</h2>";
echo "Visit this test page to check authentication: <a href='../html/test_auth_flow.html'>test_auth_flow.html</a><br>";
echo "Visit managers page: <a href='../html/managers.html'>managers.html</a><br>";

try {
    echo "\n=== TEST COMPLETED ===\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
