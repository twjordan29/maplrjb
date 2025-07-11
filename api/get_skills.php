<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../php/Database.php';
include_once '../php/Candidate.php';

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection error.']);
    exit();
}

$candidate = new Candidate($db);

$term = isset($_GET['term']) ? $_GET['term'] : '';

$skills = $candidate->getSkillsSuggestions($term);

echo json_encode($skills);
?>