<?php
session_start();
header('Content-Type: application/json');
require_once '../php/Database.php';

// Check if user is logged in and is a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    http_response_code(403);
    echo json_encode(['message' => 'Only job seekers can submit reviews.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (
    !isset($data->employer_user_id) ||
    !isset($data->rating) ||
    !isset($data->review_text) ||
    !isset($data->review_title)
) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data.']);
    exit();
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("INSERT INTO reviews (employer_user_id, job_seeker_user_id, rating, review_title, review_text, is_anonymous) VALUES (?, ?, ?, ?, ?, ?)");
$is_anonymous = isset($data->is_anonymous) && $data->is_anonymous ? 1 : 0;
$stmt->bind_param("iidsss", $data->employer_user_id, $_SESSION['user_id'], $data->rating, $data->review_title, $data->review_text, $is_anonymous);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['message' => 'Review submitted successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to submit review.']);
}
?>