<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once __DIR__ . '/../php/Database.php';
require_once __DIR__ . '/../php/User.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403); // Forbidden
    echo json_encode(array('message' => 'Access denied.'));
    exit();
}

// Note: Since we are handling file uploads, we read from $_POST, not json_decode
$data = $_POST;

// Basic validation
if (
    !isset($data['company_name']) || empty($data['company_name']) ||
    !isset($data['description']) || empty($data['description']) ||
    !isset($data['city']) || empty($data['city']) ||
    !isset($data['province']) || empty($data['province'])
) {
    http_response_code(400);
    echo json_encode(array('message' => 'Please fill in all required fields.'));
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$profileData = [
    'company_name' => $data['company_name'],
    'website' => $data['website'] ?? '',
    'description' => $data['description'],
    'location' => $data['location'] ?? '',
    'city' => $data['city'],
    'province' => $data['province'],
    'video_url' => $data['video_url'] ?? '',
    'social_links' => $data['social_links'] ?? [],
    'brand_color' => $data['brand_color'] ?? '',
    'work_for_us_desc' => $data['work_for_us_desc'] ?? ''
];

// The $_FILES superglobal contains the uploaded file information
if ($user->saveEmployerProfile($_SESSION['user_id'], $profileData, $_FILES)) {
    // While the company name isn't directly stored in the session 'user_name',
    // it's good practice to keep the session fresh if you were to add it later.
    // For now, this change primarily ensures consistency in our update logic.
    http_response_code(200);
    echo json_encode(array('message' => 'Profile saved successfully.'));
} else {
    http_response_code(503);
    echo json_encode(array('message' => 'Unable to save profile.'));
}
?>