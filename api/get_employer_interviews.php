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

$database = new Database();
$db = $database->getConnection();
$jobs = new Jobs($db);

$interviews = $jobs->getInterviewsByEmployerId($_SESSION['user_id']);

echo json_encode($interviews);
?>