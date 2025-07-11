<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../php/Database.php';
include_once '../php/User.php';

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (
    !isset($data->first_name) || empty($data->first_name) ||
    !isset($data->last_name) || empty($data->last_name) ||
    !isset($data->user_type) || !in_array($data->user_type, ['job_seeker', 'employer']) ||
    !isset($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL) ||
    !isset($data->password) || empty($data->password) ||
    !isset($data->password_confirm) || empty($data->password_confirm)
) {
    http_response_code(400);
    echo json_encode(array('message' => 'Please fill in all required fields.'));
    exit();
}

if ($data->password !== $data->password_confirm) {
    http_response_code(400);
    echo json_encode(array('message' => 'Passwords do not match.'));
    exit();
}

// Instantiate DB & connect
$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(array('message' => 'Database connection error.'));
    exit();
}

$user = new User($db);

// Check if email already exists
if ($user->emailExists($data->email)) {
    http_response_code(409); // 409 Conflict
    echo json_encode(array('message' => 'An account with this email already exists.'));
    exit();
}

// Create user
$userData = [
    'first_name' => $data->first_name,
    'last_name' => $data->last_name,
    'email' => $data->email,
    'password' => $data->password,
    'user_type' => $data->user_type
];

if ($user->create($userData)) {
    http_response_code(201); // 201 Created
    echo json_encode(array('message' => 'Account created successfully. You can now log in.'));
} else {
    http_response_code(503); // 503 Service Unavailable
    echo json_encode(array('message' => 'Unable to create account.'));
}
?>