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
$data = json_decode(file_get_contents('php://input'), true);

$conversationId = isset($data['conversation_id']) ? intval($data['conversation_id']) : 0;
$receiverId = isset($data['receiver_id']) ? intval($data['receiver_id']) : 0;
$body = isset($data['body']) ? trim($data['body']) : '';

if ($conversationId <= 0 || $receiverId <= 0 || empty($body)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid input.']);
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
    echo json_encode(['message' => 'You are not authorized to send messages in this conversation.']);
    exit;
}

$query = "INSERT INTO messages (conversation_id, sender_id, receiver_id, body) VALUES (?, ?, ?, ?)";
$stmt = $db->prepare($query);
$stmt->bind_param("iiis", $conversationId, $userId, $receiverId, $body);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['message' => 'Message sent successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to send message.']);
}
?>