<?php
// Test script to send a message to an agent
require_once '../config/database.php';

echo "<h2>🧪 Testing Message Sending to Agent</h2>";

try {
    // First, let's see what agents exist
    $stmt = $pdo->prepare("SELECT u.id, u.full_name, u.email, u.user_type FROM users u WHERE u.user_type = 'agent'");
    $stmt->execute();
    $agents = $stmt->fetchAll();
    
    echo "<h3>Available Agents:</h3>";
    if (count($agents) > 0) {
        foreach ($agents as $agent) {
            echo "<p>- ID: {$agent['id']}, Name: {$agent['full_name']}, Email: {$agent['email']}</p>";
        }
        
        // Use the first agent for testing
        $test_agent = $agents[0];
        
        // Create a test user if doesn't exist
        $test_user_email = 'testuser@propledger.com';
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$test_user_email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, user_type) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Test User', $test_user_email, password_hash('testpass', PASSWORD_DEFAULT), 'investor']);
            $user_id = $pdo->lastInsertId();
            echo "<p>✅ Created test user with ID: $user_id</p>";
        } else {
            $user_id = $user['id'];
            echo "<p>✅ Using existing test user with ID: $user_id</p>";
        }
        
        // Send a test message
        $stmt = $pdo->prepare("
            INSERT INTO manager_messages (user_id, manager_name, subject, message, priority, sender_type, receiver_type, created_at) 
            VALUES (?, ?, ?, ?, ?, 'user', 'agent', NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $test_agent['full_name'],
            'Test Message for Agent Dashboard',
            'This is a test message to verify that the agent dashboard can display messages correctly. If you can see this message in the agent dashboard, the system is working!',
            'normal'
        ]);
        
        $message_id = $pdo->lastInsertId();
        
        echo "<p>✅ Test message sent successfully!</p>";
        echo "<p><strong>Message Details:</strong></p>";
        echo "<ul>";
        echo "<li>Message ID: $message_id</li>";
        echo "<li>To Agent: {$test_agent['full_name']}</li>";
        echo "<li>From User ID: $user_id</li>";
        echo "<li>Subject: Test Message for Agent Dashboard</li>";
        echo "</ul>";
        
        // Verify the message was inserted
        $stmt = $pdo->prepare("SELECT * FROM manager_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $inserted_message = $stmt->fetch();
        
        if ($inserted_message) {
            echo "<p>✅ Message verified in database</p>";
            echo "<pre>" . print_r($inserted_message, true) . "</pre>";
        }
        
        echo "<h3>🔗 Next Steps:</h3>";
        echo "<p>1. Login as an agent with email: <strong>{$test_agent['email']}</strong></p>";
        echo "<p>2. Go to the agent dashboard</p>";
        echo "<p>3. Check if the test message appears in the Client Messages section</p>";
        
    } else {
        echo "<p>❌ No agents found in database. Please register an agent first.</p>";
        
        // Let's check if there are any users at all
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
        $stmt->execute();
        $user_count = $stmt->fetch()['count'];
        
        echo "<p>Total users in database: $user_count</p>";
        
        if ($user_count == 0) {
            echo "<p>💡 The database appears to be empty. Please:</p>";
            echo "<ol>";
            echo "<li>Run the database setup script</li>";
            echo "<li>Register some users and agents</li>";
            echo "<li>Then test the messaging system</li>";
            echo "</ol>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
