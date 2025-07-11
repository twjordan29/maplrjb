<?php
header('Content-Type: application/json');
require_once '../php/Database.php';
require_once '../php/User.php';
require_once '../php/Jobs.php';

$db = (new Database())->getConnection();
$user_obj = new User($db);
$jobs_obj = new Jobs($db);

$company_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($company_id)) {
    http_response_code(400);
    echo json_encode(['message' => 'Company ID is required.']);
    exit();
}

$company_profile = $user_obj->getEmployerProfile($company_id);

if (!$company_profile) {
    http_response_code(404);
    echo json_encode(['message' => 'Company not found.']);
    exit();
}

$company_jobs_result = $jobs_obj->getJobsByEmployer($company_id);
$company_jobs = [];
while ($row = $company_jobs_result->fetch_assoc()) {
    $company_jobs[] = $row;
}

$response = [
    'profile' => $company_profile,
    'jobs' => $company_jobs
];

echo json_encode($response);
?>