<?php
// THIS NEEDS TO BE RUN AS A CRON JOB ONCE A DAY
require_once dirname(__DIR__) . '/php/Database.php';
require_once dirname(__DIR__) . '/php/Jobs.php';

$db = (new Database())->getConnection();
$jobs_obj = new Jobs($db);

$query = "SELECT * FROM jobs WHERE is_recurring = 1 AND next_recurrence_date <= CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

while ($job = $result->fetch_assoc()) {
    // Create a new job listing based on the old one
    $jobs_obj->createJobListing(
        $job['employer_id'],
        $job['title'],
        $job['description'],
        $job['job_type'],
        $job['salary_type'],
        $job['annual_salary_min'],
        $job['annual_salary_max'],
        $job['hourly_rate_min'],
        $job['hourly_rate_max'],
        $job['location'],
        $job['city'],
        $job['province'],
        date('Y-m-d', strtotime('+30 days')), // New expiration date
        [], // No questions for reposted jobs for now
        $job['is_recurring'],
        $job['recurrence_interval']
    );

    // Update the next recurrence date for the original job
    $next_recurrence_date = null;
    if ($job['recurrence_interval'] === 'weekly') {
        $next_recurrence_date = date('Y-m-d', strtotime($job['next_recurrence_date'] . ' +1 week'));
    } elseif ($job['recurrence_interval'] === 'monthly') {
        $next_recurrence_date = date('Y-m-d', strtotime($job['next_recurrence_date'] . ' +1 month'));
    }

    if ($next_recurrence_date) {
        $update_query = "UPDATE jobs SET next_recurrence_date = ? WHERE id = ?";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bind_param("si", $next_recurrence_date, $job['id']);
        $update_stmt->execute();
    }
}

echo "Recurring jobs reposted successfully.";
?>