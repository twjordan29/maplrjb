<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

include_once '../php/Database.php';
include_once '../php/Candidate.php';
include_once '../php/Geocoding.php';

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
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['location_city']) && isset($data['location_province'])) {
    $address = $data['location_city'] . ', ' . $data['location_province'];
    $coords = Geocoding::getCoordinates($address);
    if ($coords) {
        $data['latitude'] = $coords['latitude'];
        $data['longitude'] = $coords['longitude'];
    }
}

if ($candidate->updateProfile($_SESSION['user_id'], $data)) {
    echo json_encode(['message' => 'Profile updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to update profile.']);
}
?>