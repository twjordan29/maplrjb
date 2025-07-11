<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('UTC');
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../php/Database.php';
include_once '../php/Jobs.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->getConnection();

// Check connection
if ($db === null) {
    http_response_code(500);
    echo json_encode(array('message' => 'Database connection error. Please check your credentials in php/Database.php'));
    exit();
}

// Instantiate job object
$jobs = new Jobs($db);

// Get page and perPage from query params, with defaults
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;

// Get filters from query params
$filters = [];
if (!empty($_GET['job_type'])) {
    $filters['job_type'] = $_GET['job_type'];
}
if (!empty($_GET['salary_type'])) {
    $filters['salary_type'] = $_GET['salary_type'];
}
if (!empty($_GET['salary'])) {
    $filters['salary'] = (int) $_GET['salary'];
}
if (!empty($_GET['location'])) {
    $filters['location'] = $_GET['location'];
}
if (!empty($_GET['radius'])) {
    $filters['radius'] = (int) $_GET['radius'];
}
if (!empty($_GET['keyword'])) {
    $filters['keyword'] = $_GET['keyword'];
}

// Job query
$result_data = $jobs->getAllJobs($page, $perPage, $filters);
$result = $result_data['jobs'];
$total_jobs = $result_data['total'];
$total_pages = $result_data['pages'];

// Get row count for the current page
$num = $result->num_rows;

// Check if any jobs
if ($num > 0) {
    $jobs_arr = array();
    $jobs_arr['data'] = array();
    $jobs_arr['pagination'] = array(
        'total' => $total_jobs,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $total_pages
    );

    while ($row = $result->fetch_assoc()) {
        // Construct salary string
        $salary = '';
        if ($row['salary_type'] == 'salary' && $row['annual_salary_min'] && $row['annual_salary_max']) {
            $salary = '$' . number_format($row['annual_salary_min'] / 1000) . 'K - $' . number_format($row['annual_salary_max'] / 1000) . 'K Salary';
        } elseif ($row['salary_type'] == 'hourly' && $row['hourly_rate_min'] && $row['hourly_rate_max']) {
            $salary = '$' . number_format($row['hourly_rate_min'], 2) . ' - $' . number_format($row['hourly_rate_max'], 2) . ' / hour';
        } elseif ($row['salary_type'] == 'commission') {
            $salary = 'Commission';
        } elseif ($row['salary_type'] == 'volunteer') {
            $salary = 'Volunteer';
        }

        $posted = $row['created_at'];

        $is_premium = isset($row['plan_type']) && in_array($row['plan_type'], ['premium', 'verified']);

        $company_logo_html = '';
        if ($is_premium && !empty($row['logo'])) {
            $company_logo_html = '<img src="' . htmlspecialchars($row['logo']) . '" alt="' . htmlspecialchars($row['company_name']) . ' Logo" class="company-logo-listing-lg" />';
        } else {
            $company_logo_html = '<div class="company-logo-text">' . strtoupper(substr($row['company_name'], 0, 2)) . '</div>';
        }

        $header_image = ''; // Default to no image
        if ($is_premium && !empty($row['header_image'])) {
            $header_image = htmlspecialchars($row['header_image']);
        }

        $job_item = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'company' => htmlspecialchars($row['company_name']),
            'location' => $row['city'] . ', ' . $row['province'],
            'salary' => $salary,
            'type' => ucfirst(str_replace('_', ' ', $row['job_type'])),
            'posted' => $posted,
            'description' => $row['description'],
            'company_logo_html' => $company_logo_html,
            'header_image' => $header_image,
            'is_premium' => $is_premium,
            'is_verified' => $row['plan_type'] === 'verified',
            'employer_id' => $row['employer_id']
        );

        array_push($jobs_arr['data'], $job_item);
    }

    // Turn to JSON & output
    echo json_encode($jobs_arr);

} else {
    // No Jobs
    echo json_encode(
        array('message' => 'No Jobs Found', 'data' => [], 'pagination' => array('total' => 0, 'page' => 1, 'perPage' => $perPage, 'totalPages' => 0))
    );
}
?>