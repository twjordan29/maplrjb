<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../php/Database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection error.']);
    exit();
}

$employer_id = $_SESSION['user_id'];

$query = "
    SELECT 
        u.province,
        u.education_level,
        CASE
            WHEN TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
            WHEN TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
            WHEN TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
            WHEN TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
            WHEN TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) >= 55 THEN '55+'
            ELSE NULL
        END as age_range
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.user_id = u.id
    WHERE j.employer_id = ?
";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

$location_data = [];
$education_data = [];
$age_data = [];

while ($row = $result->fetch_assoc()) {
    if (!empty($row['province'])) {
        $location_data[$row['province']] = ($location_data[$row['province']] ?? 0) + 1;
    }
    if (!empty($row['education_level'])) {
        $education_data[$row['education_level']] = ($education_data[$row['education_level']] ?? 0) + 1;
    }
    if (!empty($row['age_range'])) {
        $age_data[$row['age_range']] = ($age_data[$row['age_range']] ?? 0) + 1;
    }
}

function format_for_chart($data)
{
    $formatted = [];
    foreach ($data as $key => $value) {
        $formatted[] = ['label' => $key, 'count' => $value];
    }
    return $formatted;
}

echo json_encode([
    'location' => format_for_chart($location_data),
    'education' => format_for_chart($education_data),
    'age' => format_for_chart($age_data)
]);

?>