<?php
header('Content-Type: application/json');
require_once '../php/Database.php';
require_once '../php/Jobs.php';

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

if ($job_id <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid job ID.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$jobs = new Jobs($db);

$questions = $jobs->getQuestionsByJobId($job_id);

echo json_encode($questions);
?>