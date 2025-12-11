<?php
session_start();
require_once '../config/database.php';
require_once '../auth/check_session.php';

header('Content-Type: application/json');

try {
    // Check if user is authenticated
    $user = checkUserSession();
    if (!$user) {
        echo json_encode([
            'success' => false, 
            'message' => 'Authentication required',
            'debug' => 'No user session found'
        ]);
        exit;
    }
    
    // Debug user information
    $debug_info = [
        'user_data' => $user,
        'user_type_check' => [
            'user_type' => $user['user_type'] ?? 'not_set',
            'type' => $user['type'] ?? 'not_set', 
            'account_type' => $user['account_type'] ?? 'not_set'
        ]
    ];
    
    // Check if user is an agent
    $is_agent = ($user['user_type'] === 'agent' || $user['type'] === 'agent' || $user['account_type'] === 'agent');
    
    if (!$is_agent) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied: Agent access required',
            'debug' => $debug_info
        ]);
        exit;
    }
    
    // Check what agent name we're searching for
    $agent_name = $user['full_name'] ?? $user['name'] ?? 'Unknown';
    
    // Get all messages to see what's in the database
    $stmt = $pdo->prepare("SELECT manager_name, COUNT(*) as count FROM manager_messages GROUP BY manager_name");
    $stmt->execute();
    $all_managers = $stmt->fetchAll();
    
    // Get agent's messages (messages sent to this agent by name)
    $stmt = $pdo->prepare("
        SELECT 
            m.id, 
            m.user_id,
            m.manager_name, 
            m.subject, 
            m.message, 
            m.priority, 
            m.status, 
            m.created_at, 
            m.replied_at, 
            m.reply_message,
            u.full_name as sender_name,
            u.email as sender_email
        FROM manager_messages m
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.manager_name = ?
        ORDER BY m.created_at DESC
    ");
    
    $stmt->execute([$agent_name]);
    $messages = $stmt->fetchAll();
    
    // Count unread messages for this agent
    $unread_count = 0;
    foreach ($messages as $msg) {
        if ($msg['status'] === 'unread') {
            $unread_count++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'total_count' => count($messages),
        'unread_count' => $unread_count,
        'debug' => [
            'agent_name_searched' => $agent_name,
            'all_managers_in_db' => $all_managers,
            'user_info' => $debug_info,
            'query_executed' => true
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve messages: ' . $e->getMessage(),
        'debug' => [
            'error_details' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
