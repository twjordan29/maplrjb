<?php
session_start();
date_default_timezone_set('UTC');
header('Content-Type: application/json');
require_once '../php/Database.php';
require_once '../php/Jobs.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

if (empty($job_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'Job ID is required.']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$jobs = new Jobs($db);
$job = $jobs->getJobById($job_id);

if (!$job || $job['employer_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['message' => 'You are not authorized to view applicants for this job.']);
    exit();
}

$query = "SELECT a.id AS application_id, u.id AS user_id, u.first_name, u.last_name, u.email, u.city, u.province, a.cover_letter, a.resume_path, a.applied_at AS application_date, a.status, a.is_rejected, i.interview_datetime as interview_date FROM `applications` AS `a` JOIN `users` AS `u` ON `a`.`user_id` = `u`.`id` LEFT JOIN `interviews` i ON a.id = i.application_id WHERE `a`.`job_id` = ?";
$stmt = $db->prepare($query);
if (!$stmt) {
    error_log("Prepare failed in get_applicants.php: " . $db->error);
    http_response_code(500);
    echo json_encode(['message' => 'Database error.']);
    exit();
}
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$applicants = $result->fetch_all(MYSQLI_ASSOC);

foreach ($applicants as $key => $applicant) {
    $applicants[$key]['screening_answers'] = $jobs->getScreeningAnswers($applicant['application_id']);
}

echo json_encode($applicants);
?>