<?php
session_start();
header('Content-Type: application/json');

require_once '../php/Database.php';
require_once '../php/User.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    $response['message'] = 'You must be logged in as a job seeker to save jobs.';
    echo json_encode($response);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->job_id) || !isset($data->action)) {
    $response['message'] = 'Invalid request.';
    echo json_encode($response);
    exit();
}

$db = (new Database())->getConnection();
$user_obj = new User($db);
$userId = $_SESSION['user_id'];
$jobId = intval($data->job_id);

if ($data->action === 'save') {
    if ($user_obj->saveJob($userId, $jobId)) {
        $response['success'] = true;
        $response['message'] = 'Job saved successfully!';
    } else {
        $response['message'] = 'Failed to save job.';
    }
} elseif ($data->action === 'unsave') {
    if ($user_obj->unsaveJob($userId, $jobId)) {
        $response['success'] = true;
        $response['message'] = 'Job unsaved successfully!';
    } else {
        $response['message'] = 'Failed to unsave job.';
    }
} else {
    $response['message'] = 'Invalid action.';
}

echo json_encode($response);
?>