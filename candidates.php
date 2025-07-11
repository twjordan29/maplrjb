<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';

// If the user is not logged in or is not a premium/verified employer, redirect them.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$profileData = $user->getEmployerProfile($_SESSION['user_id']);
if (!in_array($profileData['plan_type'], ['premium', 'verified'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Candidates - Maplr.ca</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
</head>

<body>
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="dashboard-content">
            <div class="container py-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-filter me-2"></i>Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="filter-form" class="links-involved">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="keywords" class="form-label">Keywords</label>
                                    <input type="text" class="form-control" id="keywords"
                                        placeholder="e.g., PHP, Manager">
                                </div>
                                <div class="col-md-4">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location"
                                        placeholder="City or Province">
                                </div>
                                <div class="col-md-4">
                                    <label for="skills" class="form-label">Skills</label>
                                    <select class="form-select" id="skills" multiple>
                                        <!-- Skills will be populated here -->
                                    </select>
                                    <div class="form-text">Can't find a skill? <a
                                            href="https://support.maplr.ca/article/add-new-skills">Help us expand our
                                            list!</a>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="experience" class="form-label">Min Experience (Years)</label>
                                    <input type="number" class="form-control" id="experience" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label for="availability" class="form-label">Availability</label>
                                    <select class="form-select" id="availability" multiple>
                                        <option value="weekends">Weekends</option>
                                        <option value="monday_to_friday">Monday to Friday</option>
                                        <option value="days">Days</option>
                                        <option value="evenings">Evenings</option>
                                        <option value="nights">Nights</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="salary" class="form-label">Desired Salary (Annual)</label>
                                    <input type="number" class="form-control" id="salary" min="0">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="candidates-list" class="row">
                    <!-- Candidate profiles will be loaded here -->
                </div>
                <nav id="candidates-pagination" aria-label="Candidates navigation"></nav>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="js/candidates.js"></script>
</body>

</html>