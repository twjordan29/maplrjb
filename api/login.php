<?php
session_start();

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
    !isset($data->email) || !filter_var($data->email, FILTER_VALIDATE_EMAIL) ||
    !isset($data->password) || empty($data->password)
) {
    http_response_code(400);
    echo json_encode(array('message' => 'Please provide a valid email and password.'));
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

// Attempt to log in
$loggedInUser = $user->login($data->email, $data->password);

if ($loggedInUser) {
    // Set session variables
    $_SESSION['user_id'] = $loggedInUser['id'];
    $_SESSION['user_name'] = $loggedInUser['first_name'] . ' ' . $loggedInUser['last_name'];
    $_SESSION['user_type'] = $loggedInUser['user_type'];

    http_response_code(200);
    echo json_encode(array(
        'message' => 'Login successful.',
        'user' => array(
            'id' => $loggedInUser['id'],
            'name' => $_SESSION['user_name'],
            'user_type' => $loggedInUser['user_type']
        )
    ));
} else {
    http_response_code(401); // 401 Unauthorized
    echo json_encode(array('message' => 'Login failed. Invalid email or password.'));
}
?>