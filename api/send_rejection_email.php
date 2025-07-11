<?php
session_start();
header('Content-Type: application/json');
require_once '../php/Database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->application_id) || !isset($data->subject) || !isset($data->body)) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data.']);
    exit();
}

// In a real application, you would integrate with an email sending service like PHPMailer or SendGrid.
// For this example, we'll just simulate a successful email send.

$db = (new Database())->getConnection();
$stmt = $db->prepare("UPDATE applications SET is_rejected = 1 WHERE id = ?");
$stmt->bind_param("i", $data->application_id);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
?>