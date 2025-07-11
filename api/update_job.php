<?php
session_start();
header('Content-Type: application/json');

require_once '../php/Database.php';
require_once '../php/Jobs.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    echo json_encode(['message' => 'You are not authorized to perform this action.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$jobs = new Jobs($db);

// Get the posted data
$job_id = isset($_POST['id']) ? $_POST['id'] : null;
$title = isset($_POST['title']) ? $_POST['title'] : null;
$description = isset($_POST['description']) ? $_POST['description'] : null;
$job_type = isset($_POST['job_type']) ? $_POST['job_type'] : null;
$salary_type = isset($_POST['salary_type']) ? $_POST['salary_type'] : null;
$annual_salary_min = isset($_POST['annual_salary_min']) ? $_POST['annual_salary_min'] : null;
$annual_salary_max = isset($_POST['annual_salary_max']) ? $_POST['annual_salary_max'] : null;
$hourly_rate_min = isset($_POST['hourly_rate_min']) ? $_POST['hourly_rate_min'] : null;
$hourly_rate_max = isset($_POST['hourly_rate_max']) ? $_POST['hourly_rate_max'] : null;
$location = isset($_POST['location']) ? $_POST['location'] : null;
$city = isset($_POST['city']) ? $_POST['city'] : null;
$province = isset($_POST['province']) ? $_POST['province'] : null;
$expires_at = isset($_POST['expires_at']) ? $_POST['expires_at'] : null;

if (!$job_id || !$title || !$description || !$job_type || !$salary_type || !$city || !$province) {
    http_response_code(400);
    echo json_encode(['message' => 'Please fill in all required fields.']);
    exit;
}

// Check if the job belongs to the current user
$job = $jobs->getJobById($job_id);
if (!$job || $job['employer_id'] !== $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['message' => 'You are not authorized to update this job.']);
    exit;
}

$questions = isset($_POST['questions']) ? $_POST['questions'] : [];
$is_recurring = isset($_POST['is_recurring']);
$recurrence_interval = $_POST['recurrence_interval'] ?? null;

if ($jobs->updateJob($job_id, $title, $description, $job_type, $salary_type, $annual_salary_min, $annual_salary_max, $hourly_rate_min, $hourly_rate_max, $location, $city, $province, $expires_at, $questions, $is_recurring, $recurrence_interval)) {
    http_response_code(200);
    echo json_encode(['message' => 'Job updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Unable to update job.']);
}
?>