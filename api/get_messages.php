<?php
session_start();
header('Content-Type: application/json');

require_once '../php/Database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];
$conversationId = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

if ($conversationId <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid conversation ID.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Verify the user is part of this conversation
$verifyQuery = "SELECT id FROM conversations WHERE id = ? AND (user_one = ? OR user_two = ?)";
$verifyStmt = $db->prepare($verifyQuery);
$verifyStmt->bind_param("iii", $conversationId, $userId, $userId);
$verifyStmt->execute();
$verifyResult = $verifyStmt->get_result();

if ($verifyResult->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['message' => 'You are not authorized to view this conversation.']);
    exit;
}

// Fetch messages
$query = "SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $conversationId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>