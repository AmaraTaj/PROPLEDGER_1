<?php
require_once '../config/database.php';
require_once '../config/oauth_config.php';

echo "<h1>🔧 OAuth Setup Test</h1>";

// Test 1: Check database tables
echo "<h2>1. Database Tables Check</h2>";
try {
    // Check oauth_states table
    $stmt = $pdo->query("DESCRIBE oauth_states");
    echo "<p>✅ oauth_states table exists</p>";
    
    // Check users table OAuth columns
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $oauth_columns = ['oauth_provider', 'oauth_id', 'profile_picture_url', 'email_verified'];
    foreach ($oauth_columns as $col) {
        if (in_array($col, $columns)) {
            echo "<p>✅ users.$col column exists</p>";
        } else {
            echo "<p>❌ users.$col column missing</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 2: Check OAuth configuration
echo "<h2>2. OAuth Configuration Check</h2>";
global $oauth_config;
foreach ($oauth_config as $provider => $config) {
    $configured = !str_contains($config['client_id'], 'YOUR_');
    $status = $configured ? '✅ Configured' : '⚠️ Needs Configuration';
    echo "<p><strong>" . ucfirst($provider) . ":</strong> $status</p>";
    
    if (!$configured) {
        echo "<p style='margin-left: 20px; color: #666;'>Client ID: {$config['client_id']}</p>";
    }
}

// Test 3: Test OAuth state generation
echo "<h2>3. OAuth State Generation Test</h2>";
try {
    $state = generateOAuthState('google');
    echo "<p>✅ OAuth state generated successfully: " . substr($state, 0, 16) . "...</p>";
    
    // Validate the state
    $valid = validateOAuthState($state, 'google');
    echo "<p>✅ OAuth state validation: " . ($valid ? 'PASSED' : 'FAILED') . "</p>";
} catch (Exception $e) {
    echo "<p>❌ OAuth state error: " . $e->getMessage() . "</p>";
}

// Test 4: Test OAuth URL generation
echo "<h2>4. OAuth URL Generation Test</h2>";
try {
    foreach (['google', 'linkedin', 'facebook'] as $provider) {
        $configured = !str_contains($oauth_config[$provider]['client_id'], 'YOUR_');
        if ($configured) {
            $url = getOAuthAuthUrl($provider);
            echo "<p>✅ $provider OAuth URL generated successfully</p>";
            echo "<p style='margin-left: 20px; font-size: 0.9em; color: #666;'>" . substr($url, 0, 80) . "...</p>";
        } else {
            echo "<p>⚠️ $provider OAuth URL - needs configuration</p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ OAuth URL error: " . $e->getMessage() . "</p>";
}

echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Configure OAuth Providers:</strong> Edit <code>config/oauth_config.php</code> with your OAuth app credentials</li>";
echo "<li><strong>Test OAuth Flow:</strong> Try the OAuth buttons after configuration</li>";
echo "<li><strong>For Testing:</strong> You can use Google OAuth test credentials for development</li>";
echo "</ol>";

echo "<h3>🔗 Quick Links</h3>";
echo "<p><a href='../config/oauth_config.php' target='_blank'>Configure OAuth Settings</a></p>";
echo "<p><a href='../html/login.html' target='_blank'>Test Login Page</a></p>";
echo "<p><a href='../html/signup.html' target='_blank'>Test Signup Page</a></p>";
?>
