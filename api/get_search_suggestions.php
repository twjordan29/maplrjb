<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../php/Database.php';
include_once '../php/Jobs.php';

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection error.']);
    exit();
}

$jobs = new Jobs($db);

$term = isset($_GET['term']) ? $_GET['term'] : '';

if (empty($term)) {
    echo json_encode([]);
    exit();
}

$suggestions = $jobs->getSearchSuggestions($term);

echo json_encode($suggestions);
?>