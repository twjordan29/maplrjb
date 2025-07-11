<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../php/Database.php';

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(array('message' => 'Database connection error.'));
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->job_id) || !isset($data->event_type)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Incomplete data.'));
    exit();
}

$job_id = $data->job_id;
$event_type = $data->event_type;

if (!in_array($event_type, ['view', 'click'])) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid event type.'));
    exit();
}

$query = "INSERT INTO job_analytics (job_id, event_type) VALUES (?, ?)";

$stmt = $db->prepare($query);
$stmt->bind_param("is", $job_id, $event_type);

if ($stmt->execute()) {
    echo json_encode(array('success' => true));
} else {
    http_response_code(500);
    echo json_encode(array('message' => 'Event logging failed.'));
}
?>