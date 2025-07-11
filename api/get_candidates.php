<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
header('Content-Type: application/json');
require_once '../php/Database.php';
require_once '../php/Geocoding.php';

$db = (new Database())->getConnection();

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$base_query = "SELECT c.*, u.first_name, u.last_name, u.profile_picture FROM candidates c JOIN users u ON c.user_id = u.id WHERE c.is_searchable = 1";
$count_query = "SELECT COUNT(DISTINCT c.id) as total FROM candidates c JOIN users u ON c.user_id = u.id WHERE c.is_searchable = 1";
$params = [];
$types = '';

if (!empty($_GET['keywords'])) {
    $keywords = '%' . $_GET['keywords'] . '%';
    $base_query .= " AND (c.headline LIKE ? OR c.summary LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR c.id IN (SELECT cs.candidate_id FROM candidate_skills cs JOIN skills s ON cs.skill_id = s.id WHERE s.name LIKE ?))";
    $count_query .= " AND (c.headline LIKE ? OR c.summary LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR c.id IN (SELECT cs.candidate_id FROM candidate_skills cs JOIN skills s ON cs.skill_id = s.id WHERE s.name LIKE ?))";
    for ($i = 0; $i < 5; $i++) {
        $params[] = $keywords;
        $types .= 's';
    }
}

if (!empty($_GET['location'])) {
    $coords = Geocoding::getCoordinates($_GET['location']);
    if ($coords) {
        $lat = $coords['latitude'];
        $lng = $coords['longitude'];
        $radius = 50; // 50km radius
        $base_query .= " AND (6371 * acos(cos(radians(?)) * cos(radians(c.latitude)) * cos(radians(c.longitude) - radians(?)) + sin(radians(?)) * sin(radians(c.latitude)))) < ?";
        $count_query .= " AND (6371 * acos(cos(radians(?)) * cos(radians(c.latitude)) * cos(radians(c.longitude) - radians(?)) + sin(radians(?)) * sin(radians(c.latitude)))) < ?";
        $params[] = $lat;
        $params[] = $lng;
        $params[] = $lat;
        $params[] = $radius;
        $types .= 'dddi';
    }
}

if (!empty($_GET['experience_years'])) {
    $base_query .= " AND c.experience_years >= ?";
    $count_query .= " AND c.experience_years >= ?";
    $params[] = (int) $_GET['experience_years'];
    $types .= 'i';
}

if (!empty($_GET['availability']) && is_array($_GET['availability'])) {
    $availability_conditions = [];
    foreach ($_GET['availability'] as $availability) {
        $availability_conditions[] = "FIND_IN_SET(?, c.availability)";
        $params[] = $availability;
        $types .= 's';
    }
    if (!empty($availability_conditions)) {
        $base_query .= " AND (" . implode(' OR ', $availability_conditions) . ")";
        $count_query .= " AND (" . implode(' OR ', $availability_conditions) . ")";
    }
}

if (!empty($_GET['desired_salary_min'])) {
    $base_query .= " AND c.desired_salary_min >= ?";
    $count_query .= " AND c.desired_salary_min >= ?";
    $params[] = (int) $_GET['desired_salary_min'];
    $types .= 'i';
}

if (!empty($_GET['skills']) && is_array($_GET['skills'])) {
    $skills_placeholders = implode(',', array_fill(0, count($_GET['skills']), '?'));
    $base_query .= " AND c.id IN (SELECT candidate_id FROM candidate_skills WHERE skill_id IN ($skills_placeholders))";
    $count_query .= " AND c.id IN (SELECT candidate_id FROM candidate_skills WHERE skill_id IN ($skills_placeholders))";
    foreach ($_GET['skills'] as $skill) {
        $params[] = $skill;
        $types .= 'i';
    }
}

// Count total results
$stmt = $db->prepare($count_query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_results = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Get paginated results
$paginated_query = $base_query . " GROUP BY c.id LIMIT ? OFFSET ?";
$pagination_params = $params;
$pagination_types = $types;

$pagination_params[] = $limit;
$pagination_params[] = $offset;
$pagination_types .= 'ii';

$stmt = $db->prepare($paginated_query);
if ($pagination_types) {
    $stmt->bind_param($pagination_types, ...$pagination_params);
}
$stmt->execute();
$result = $stmt->get_result();
$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}

$candidate_ids = array_map(fn($c) => $c['id'], $candidates);

if (!empty($candidate_ids)) {
    $skills_query = "SELECT cs.candidate_id, s.name FROM candidate_skills cs JOIN skills s ON cs.skill_id = s.id WHERE cs.candidate_id IN (" . implode(',', array_fill(0, count($candidate_ids), '?')) . ")";
    $stmt_skills = $db->prepare($skills_query);
    $types_skills = str_repeat('i', count($candidate_ids));
    $stmt_skills->bind_param($types_skills, ...$candidate_ids);
    $stmt_skills->execute();
    $skills_result = $stmt_skills->get_result();
    $skills_by_candidate = [];
    while ($row = $skills_result->fetch_assoc()) {
        $skills_by_candidate[$row['candidate_id']][] = $row['name'];
    }

    foreach ($candidates as &$candidate) {
        $candidate['skills'] = $skills_by_candidate[$candidate['id']] ?? [];
    }
}

echo json_encode([
    'candidates' => $candidates,
    'total_pages' => $total_pages,
    'current_page' => $page
]);
?>