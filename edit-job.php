<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';
require_once 'php/Jobs.php';

// If the user is not logged in or is not an employer, redirect them.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header('Location: login.html');
    exit();
}

$job_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$job_id) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$jobs = new Jobs($db);
$job = $jobs->getJobById($job_id);
$job['questions'] = $jobs->getQuestionsByJobId($job_id);

// Check if the job exists and belongs to the current user
if (!$job || $job['employer_id'] !== $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - Maplr.ca</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>

<body>
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="dashboard-content">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="my-3">Edit Job</h3>
                            </div>
                            <div class="card-body">
                                <div id="message-container"></div>
                                <form id="editJobForm">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($job['id']); ?>">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Job Title</label>
                                        <input type="text" class="form-control" id="title" name="title"
                                            value="<?php echo htmlspecialchars($job['title']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Job Description</label>
                                        <div id="description" class="quill-editor" style="height: 200px;">
                                            <?php echo $job['description']; ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="job_type" class="form-label">Job Type</label>
                                            <select class="form-select" id="job_type" name="job_type" required>
                                                <option value="full_time" <?php echo ($job['job_type'] == 'full_time') ? 'selected' : ''; ?>>Full-Time</option>
                                                <option value="part_time" <?php echo ($job['job_type'] == 'part_time') ? 'selected' : ''; ?>>Part-Time</option>
                                                <option value="contract" <?php echo ($job['job_type'] == 'contract') ? 'selected' : ''; ?>>Contract</option>
                                                <option value="remote" <?php echo ($job['job_type'] == 'remote') ? 'selected' : ''; ?>>Remote</option>
                                                <option value="internship" <?php echo ($job['job_type'] == 'internship') ? 'selected' : ''; ?>>Internship</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="salary_type" class="form-label">Salary Type</label>
                                            <select class="form-select" id="salary_type" name="salary_type" required>
                                                <option value="salary" <?php echo ($job['salary_type'] == 'salary') ? 'selected' : ''; ?>>Salary</option>
                                                <option value="hourly" <?php echo ($job['salary_type'] == 'hourly') ? 'selected' : ''; ?>>Hourly</option>
                                                <option value="commission" <?php echo ($job['salary_type'] == 'commission') ? 'selected' : ''; ?>>Commission
                                                </option>
                                                <option value="volunteer" <?php echo ($job['salary_type'] == 'volunteer') ? 'selected' : ''; ?>>Volunteer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="salaryFields">
                                        <!-- Salary fields will be dynamically inserted here -->
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="location" class="form-label">Location (e.g. Office
                                                Address)</label>
                                            <input type="text" class="form-control" id="location" name="location"
                                                value="<?php echo htmlspecialchars($job['location']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                value="<?php echo htmlspecialchars($job['city']); ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="province" class="form-label">Province</label>
                                            <select class="form-select" id="province" name="province" required>
                                                <option value="">Select Province</option>
                                                <?php
                                                require 'includes/provinces.php';
                                                foreach ($provinces as $abbr => $name) {
                                                    $selected = ($job['province'] == $name) ? 'selected' : '';
                                                    echo "<option value=\"$name\" $selected>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="expires_at" class="form-label">Application Deadline</label>
                                        <input type="date" class="form-control" id="expires_at" name="expires_at"
                                            value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($job['expires_at']))); ?>">
                                    </div>

                                    <?php
                                    $user_obj = new User($db);
                                    $employer_profile = $user_obj->getEmployerProfile($_SESSION['user_id']);
                                    if ($employer_profile && in_array($employer_profile['plan_type'], ['premium', 'verified'])):
                                        ?>
                                        <div class="my-4">
                                            <h5><i class="fas fa-question-circle me-2"></i>Screening Questions</h5>
                                            <p class="text-muted small">Add questions for applicants to answer when they
                                                apply.</p>
                                            <div id="questions-container">
                                                <!-- Questions will be added here -->
                                            </div>
                                            <button type="button" id="add-question-btn"
                                                class="btn btn-sm btn-outline-secondary mt-2">
                                                <i class="fas fa-plus me-1"></i> Add Question
                                            </button>
                                        </div>
                                        <div id="recurrence-section">
                                            <hr>
                                            <h5 class="mb-3">Recurring Listing</h5>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_recurring"
                                                    name="is_recurring" <?php echo $job['is_recurring'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="is_recurring">
                                                    Automatically repost this job
                                                </label>
                                            </div>
                                            <div id="recurrence-options" class="mt-3"
                                                style="<?php echo $job['is_recurring'] ? '' : 'display: none;'; ?>">
                                                <label for="recurrence_interval" class="form-label">Repost every</label>
                                                <select class="form-select" id="recurrence_interval"
                                                    name="recurrence_interval">
                                                    <option value="weekly" <?php echo ($job['recurrence_interval'] == 'weekly') ? 'selected' : ''; ?>>Week
                                                    </option>
                                                    <option value="monthly" <?php echo ($job['recurrence_interval'] == 'monthly') ? 'selected' : ''; ?>>Month
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary mt-3">Update Job</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="js/edit-job.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>
<script>
    const jobData = <?php echo json_encode($job); ?>;
</script>