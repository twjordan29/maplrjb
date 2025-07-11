<?php
session_start();
header('Content-Type: application/json');
require_once '../php/Database.php';
require_once '../php/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

$user_obj = new User((new Database())->getConnection());
$employer_profile = $user_obj->getEmployerProfile($_SESSION['user_id']);
if ($employer_profile['plan_type'] !== 'verified') {
    http_response_code(403);
    echo json_encode(['message' => 'Only verified employers can schedule interviews.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->application_id) || !isset($data->job_seeker_id) || !isset($data->interview_datetime)) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data.']);
    exit();
}

$application_id = $data->application_id;
$job_seeker_id = $data->job_seeker_id;
$interview_datetime = $data->interview_datetime;
$notes = $data->notes ?? null;
$employer_id = $_SESSION['user_id'];

$db = (new Database())->getConnection();
$query = "INSERT INTO interviews (application_id, employer_id, job_seeker_id, interview_datetime, notes) VALUES (?, ?, ?, ?, ?)";
$stmt = $db->prepare($query);
$stmt->bind_param("iiiss", $application_id, $employer_id, $job_seeker_id, $interview_datetime, $notes);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Interview scheduled successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to schedule interview.']);
}
?>