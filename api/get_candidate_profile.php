<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../php/Database.php';
include_once '../php/Candidate.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'User not logged in.']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection error.']);
    exit();
}

$candidate = new Candidate($db);
$profile = $candidate->getProfile($_SESSION['user_id']);

if (empty($profile)) {
    http_response_code(404);
    echo json_encode(['message' => 'Profile not found.']);
    exit();
}

echo json_encode($profile);
?>