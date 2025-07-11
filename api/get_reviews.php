<?php
header('Content-Type: application/json');
require_once '../php/Database.php';
require_once '../php/User.php';

$employer_user_id = isset($_GET['employer_id']) ? intval($_GET['employer_id']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$reviews_per_page = 5;
$offset = ($page - 1) * $reviews_per_page;

if (!$employer_user_id) {
    http_response_code(400);
    echo json_encode(['message' => 'Employer ID is required.']);
    exit();
}

$db = (new Database())->getConnection();
$user_obj = new User($db);

// Get total number of reviews
$total_reviews_stmt = $db->prepare("SELECT COUNT(*) as total FROM reviews WHERE employer_user_id = ?");
$total_reviews_stmt->bind_param("i", $employer_user_id);
$total_reviews_stmt->execute();
$total_reviews_result = $total_reviews_stmt->get_result();
$total_reviews = $total_reviews_result->fetch_assoc()['total'];
$total_pages = ceil($total_reviews / $reviews_per_page);

// Get reviews for the current page
$stmt = $db->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.job_seeker_user_id = u.id WHERE r.employer_user_id = ? ORDER BY r.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $employer_user_id, $reviews_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$reviews = [];
while ($row = $result->fetch_assoc()) {
    if ($row['is_anonymous']) {
        $row['first_name'] = 'Anonymous';
        $row['last_name'] = '';
    } else {
        $row['last_name'] = substr($row['last_name'], 0, 1) . '.';
    }
    $reviews[] = $row;
}

// Get average rating
$stmt = $db->prepare("SELECT AVG(rating) as average_rating FROM reviews WHERE employer_user_id = ?");
$stmt->bind_param("i", $employer_user_id);
$stmt->execute();
$result = $stmt->get_result();
$average_rating = $result->fetch_assoc()['average_rating'];

echo json_encode(['reviews' => $reviews, 'average_rating' => number_format($average_rating, 1), 'total_pages' => $total_pages, 'current_page' => $page]);
?>