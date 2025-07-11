<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';

$db = new Database();
$db = $db->getConnection();

// If the user is not logged in or is not an employer, redirect them.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Job - Maplr.ca</title>
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
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="my-3">Post a New Job</h3>
                            </div>
                            <div class="card-body">
                                <div id="message-container"></div>
                                <form id="postJobForm">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Job Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Job Description</label>
                                        <div id="description" class="quill-editor" style="height: 200px;"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="job_type" class="form-label">Job Type</label>
                                            <select class="form-select" id="job_type" name="job_type" required>
                                                <option value="full_time">Full-Time</option>
                                                <option value="part_time">Part-Time</option>
                                                <option value="contract">Contract</option>
                                                <option value="remote">Remote</option>
                                                <option value="internship">Internship</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="salary_type" class="form-label">Salary Type</label>
                                            <select class="form-select" id="salary_type" name="salary_type" required>
                                                <option value="salary">Salary</option>
                                                <option value="hourly">Hourly</option>
                                                <option value="commission">Commission</option>
                                                <option value="volunteer">Volunteer</option>
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
                                            <input type="text" class="form-control" id="location" name="location">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="province" class="form-label">Province</label>
                                            <select class="form-select" id="province" name="province" required>
                                                <option value="">Select Province</option>
                                                <?php
                                                require 'includes/provinces.php';
                                                foreach ($provinces as $abbr => $name) {
                                                    echo "<option value=\"$name\">$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="expires_at" class="form-label">Application Deadline</label>
                                        <input type="date" class="form-control" id="expires_at" name="expires_at">
                                    </div>

                                    <div id="recurrence-section" style="display: none;">
                                        <hr>
                                        <h5 class="mb-3">Recurring Listing</h5>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_recurring"
                                                name="is_recurring">
                                            <label class="form-check-label" for="is_recurring">
                                                Automatically repost this job
                                            </label>
                                        </div>
                                        <div id="recurrence-options" class="mt-3" style="display: none;">
                                            <label for="recurrence_interval" class="form-label">Repost every</label>
                                            <select class="form-select" id="recurrence_interval"
                                                name="recurrence_interval">
                                                <option value="weekly">Week</option>
                                                <option value="monthly">Month</option>
                                            </select>
                                        </div>
                                    </div>

                                </form>

                                <?php
                                $database = new Database();
                                $db = $database->getConnection();
                                $user_obj = new User($db);
                                $employer_profile = $user_obj->getEmployerProfile($_SESSION['user_id']);
                                if ($employer_profile && in_array($employer_profile['plan_type'], ['premium', 'verified'])):
                                    ?>
                                    <div class="my-4">
                                        <h5><i class="fas fa-question-circle me-2"></i>Screening Questions</h5>
                                        <p class="text-muted small">Add questions for applicants to answer when they apply.
                                        </p>
                                        <div id="questions-container">
                                            <!-- Questions will be added here -->
                                        </div>
                                        <button type="button" id="add-question-btn"
                                            class="btn btn-sm btn-outline-secondary mt-2">
                                            <i class="fas fa-plus me-1"></i> Add Question
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" form="postJobForm" class="btn btn-primary mt-3">Post Job</button>
                            </div> <!-- end card-body -->
                        </div> <!-- end card -->
                    </div> <!-- end col-lg-8 -->

                    <div class="col-lg-4">
                        <div class="card tips-card">
                            <div class="card-header">
                                <h5 class="my-3"><i class="fas fa-lightbulb me-2"></i>Tips for a Great Job Post</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Use a clear and concise title.</strong>
                                        <p class="small text-muted">Good: "Senior PHP Developer". Bad: "PHP Guru Needed
                                            ASAP!".
                                        </p>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Sell the opportunity.</strong>
                                        <p class="small text-muted">Describe your company culture and what makes it a
                                            great
                                            place to
                                            work.</p>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Be specific about requirements.</strong>
                                        <p class="small text-muted">List necessary skills, experience level, and
                                            qualifications.
                                        </p>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Include salary information.</strong>
                                        <p class="small text-muted">Posts with salary ranges get more qualified
                                            applicants.</p>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Proofread your post.</strong>
                                        <p class="small text-muted">Avoid typos and grammatical errors to look
                                            professional.</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row -->
            </div> <!-- end container -->
        </main>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/post-job.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>