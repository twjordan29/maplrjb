<?php
session_start();
header('Content-Type: application/json');
require_once '../php/Database.php';
require_once '../php/User.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    echo json_encode(['message' => 'Only employers can respond to reviews.']);
    exit();
}

$user_obj = new User((new Database())->getConnection());
$employer_profile = $user_obj->getEmployerProfile($_SESSION['user_id']);

if (!in_array($employer_profile['plan_type'], ['premium', 'verified'])) {
    http_response_code(403);
    echo json_encode(['message' => 'Only premium and verified employers can respond to reviews.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (
    !isset($data->review_id) ||
    !isset($data->response_text)
) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data.']);
    exit();
}

$db = (new Database())->getConnection();

// Verify that the review belongs to the employer
$stmt = $db->prepare("SELECT id FROM reviews WHERE id = ? AND employer_user_id = ?");
$stmt->bind_param("ii", $data->review_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['message' => 'You do not have permission to respond to this review.']);
    exit();
}


$stmt = $db->prepare("UPDATE reviews SET employer_response = ? WHERE id = ?");
$stmt->bind_param("si", $data->response_text, $data->review_id);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['message' => 'Response submitted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to submit response.']);
}
?>