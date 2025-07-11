<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../php/Database.php';
include_once '../php/Jobs.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    echo json_encode(array('message' => 'Unauthorized'));
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(array('message' => 'Database connection error.'));
    exit();
}

$jobs_obj = new Jobs($db);
$employer_jobs = $jobs_obj->getJobsByEmployer($_SESSION['user_id']);

$analytics_data = [];

while ($job = $employer_jobs->fetch_assoc()) {
    $job_id = $job['id'];

    // Get views
    $view_query = "SELECT COUNT(*) as count FROM job_analytics WHERE job_id = ? AND event_type = 'view'";
    $view_stmt = $db->prepare($view_query);
    $view_stmt->bind_param("i", $job_id);
    $view_stmt->execute();
    $views = $view_stmt->get_result()->fetch_assoc()['count'];

    // Get clicks
    $click_query = "SELECT COUNT(*) as count FROM job_analytics WHERE job_id = ? AND event_type = 'click'";
    $click_stmt = $db->prepare($click_query);
    $click_stmt->bind_param("i", $job_id);
    $click_stmt->execute();
    $clicks = $click_stmt->get_result()->fetch_assoc()['count'];

    // Get applications
    $app_query = "SELECT COUNT(*) as count FROM applications WHERE job_id = ?";
    $app_stmt = $db->prepare($app_query);
    $app_stmt->bind_param("i", $job_id);
    $app_stmt->execute();
    $applications = $app_stmt->get_result()->fetch_assoc()['count'];

    $analytics_data[] = [
        'job_title' => $job['title'],
        'views' => $views,
        'clicks' => $clicks,
        'applications' => $applications
    ];
}

echo json_encode($analytics_data);
?>