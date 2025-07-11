<?php
session_start();
header('Content-Type: application/json');
require_once '../php/Database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized']);
    exit();
}

$job_seeker_id = $_SESSION['user_id'];

$db = (new Database())->getConnection();
$query = "
    SELECT 
        i.id as interview_id,
        i.interview_datetime,
        i.status,
        i.notes,
        j.title as job_title,
        e.company_name
    FROM interviews i
    JOIN applications a ON i.application_id = a.id
    JOIN jobs j ON a.job_id = j.id
    JOIN employers e ON j.employer_id = e.user_id
    WHERE i.job_seeker_id = ?
    ORDER BY i.interview_datetime DESC
";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $job_seeker_id);
$stmt->execute();
$result = $stmt->get_result();
$interviews = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($interviews);
?>