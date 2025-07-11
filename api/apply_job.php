<?php
session_start();
header('Content-Type: application/json');

require_once '../php/Database.php';
require_once '../php/Jobs.php';

$response = ['success' => false, 'message' => 'An error occurred.'];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to apply.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    $jobs = new Jobs($db);

    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    $answers = $_POST['answers'] ?? [];

    if (empty($job_id) || empty($name) || empty($email) || empty($cover_letter)) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit();
    }

    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Resume upload failed.';
        echo json_encode($response);
        exit();
    }

    $upload_dir = '../uploads/resumes/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx'];

    if (!in_array($file_ext, $allowed_ext)) {
        $response['message'] = 'Invalid file type. Only PDF, DOC, and DOCX are allowed.';
        echo json_encode($response);
        exit();
    }

    $resume_filename = uniqid() . '-' . basename($_FILES['resume']['name']);
    $resume_path = $upload_dir . $resume_filename;

    if (move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
        if ($jobs->createApplication($job_id, $user_id, $name, $email, $cover_letter, $resume_path, $answers)) {
            $response['success'] = true;
            $response['message'] = 'Application submitted successfully!';
        } else {
            $response['message'] = 'Failed to save application to the database.';
        }
    } else {
        $response['message'] = 'Failed to move uploaded file.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>