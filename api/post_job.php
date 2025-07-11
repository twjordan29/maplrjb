<?php
session_start();
date_default_timezone_set('UTC');
header('Content-Type: application/json');

require_once '../php/Database.php';
require_once '../php/Jobs.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $job = new Jobs($db);

    $employer_id = $_SESSION['user_id'];
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $job_type = $_POST['job_type'] ?? null;
    $salary_type = $_POST['salary_type'] ?? null;
    $annual_salary_min = !empty($_POST['annual_salary_min']) ? $_POST['annual_salary_min'] : null;
    $annual_salary_max = !empty($_POST['annual_salary_max']) ? $_POST['annual_salary_max'] : null;
    $hourly_rate_min = !empty($_POST['hourly_rate_min']) ? $_POST['hourly_rate_min'] : null;
    $hourly_rate_max = !empty($_POST['hourly_rate_max']) ? $_POST['hourly_rate_max'] : null;
    $location = $_POST['location'] ?? null;
    $city = $_POST['city'] ?? null;
    $province = $_POST['province'] ?? null;
    $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
    $questions = isset($_POST['questions']) ? $_POST['questions'] : [];
    $is_recurring = isset($_POST['is_recurring']);
    $recurrence_interval = $_POST['recurrence_interval'] ?? null;

    if ($title && $description && $job_type && $salary_type && $city && $province) {
        if ($job->createJobListing($employer_id, $title, $description, $job_type, $salary_type, $annual_salary_min, $annual_salary_max, $hourly_rate_min, $hourly_rate_max, $location, $city, $province, $expires_at, $questions, $is_recurring, $recurrence_interval)) {
            http_response_code(200);
            $response['status'] = 'success';
            $response['message'] = 'Job posted successfully!';
        } else {
            http_response_code(500);
            $response['message'] = 'Failed to post job. Please try again.';
        }
    } else {
        http_response_code(400);
        $response['message'] = 'Please fill in all required fields.';
    }
} else {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);