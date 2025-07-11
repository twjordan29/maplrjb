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

if (!isset($data->application_id) || !isset($data->status)) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data.']);
    exit();
}

$application_id = $data->application_id;
$status = $data->status;
$allowed_statuses = ['new', 'under-review', 'interview', 'not-suitable', 'hired'];

if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid status.']);
    exit();
}

// Convert status from kebab-case to "Title Case" for the database
$status_temp = str_replace('-', ' ', $status);
$db_status = ucwords($status_temp);

$db = (new Database())->getConnection();

// We should verify that the employer owns the job associated with this application
$query = "UPDATE applications SET status = ? WHERE id = ? AND job_id IN (SELECT id FROM jobs WHERE employer_id = ?)";
$stmt = $db->prepare($query);
$stmt->bind_param("sii", $db_status, $application_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Application status updated successfully.']);
    } else {
        http_response_code(403);
        echo json_encode(['message' => 'You are not authorized to update this application.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to update application status.']);
}
?>