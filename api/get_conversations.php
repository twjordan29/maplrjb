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

$database = new Database();
$db = $database->getConnection();

$query = "SELECT
            c.id as conversation_id,
            u.id as other_user_id,
            CONCAT(u.first_name, ' ', u.last_name) as other_user_name,
            (SELECT body FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
            (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time
          FROM conversations c
          JOIN users u ON u.id = IF(c.user_one = ?, c.user_two, c.user_one)
          WHERE c.user_one = ? OR c.user_two = ?
          ORDER BY last_message_time DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("iii", $userId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}

echo json_encode($conversations);
?>