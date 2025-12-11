<?php
// Direct test of get_agents.php API
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
</head>
<body>
    <h1>Testing get_agents.php API</h1>
    
    <h2>Direct PHP Output:</h2>
    <?php
    require_once '../config/database.php';
    
    try {
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
        
        echo "<p>Found " . count($agents) . " agents:</p>";
        echo "<ul>";
        foreach ($agents as $agent) {
            echo "<li>{$agent['full_name']} - {$agent['city']} - {$agent['status']}</li>";
        }
        echo "</ul>";
        
        $response = [
            'success' => true,
            'agents' => $agents,
            'count' => count($agents)
        ];
        
        echo "<h3>JSON Response:</h3>";
        echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
        
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <h2>JavaScript Fetch Test:</h2>
    <button onclick="testAPI()">Test API Call</button>
    <div id="result"></div>
    
    <script>
    async function testAPI() {
        try {
            console.log('Testing API call...');
            const response = await fetch('../managers/get_agents.php');
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('API Response:', data);
            
            document.getElementById('result').innerHTML = `
                <h3>API Response:</h3>
                <p>Success: ${data.success}</p>
                <p>Count: ${data.count}</p>
                <p>Agents: ${data.agents ? data.agents.length : 0}</p>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
        } catch (error) {
            console.error('API Error:', error);
            document.getElementById('result').innerHTML = `<p>Error: ${error.message}</p>`;
        }
    }
    
    // Auto-test on load
    window.onload = function() {
        testAPI();
    };
    </script>
</body>
</html>
