<?php
session_start();
header('Content-Type: application/json');

require_once '../php/Database.php';
require_once '../php/User.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    $response['message'] = 'You must be logged in as a job seeker.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    $user_obj = new User($db);
    $userId = $_SESSION['user_id'];

    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'province' => trim($_POST['province'] ?? ''),
        'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
        'education_level' => trim($_POST['education_level'] ?? '')
    ];

    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit();
    }

    if ($user_obj->updateJobSeekerProfile($userId, $data, $_FILES)) {
        // Update session variables
        $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];

        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
    } else {
        $response['message'] = 'Failed to update profile.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>