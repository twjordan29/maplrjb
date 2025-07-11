<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs - Maplr.ca</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-maple-leaf maple-leaf"></i>
                Maplr.ca
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="jobs.php">Find Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#companies">Companies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-primary" href="login.html">Sign In</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="signup.html">Post a Job</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Job Filters Bar -->
    <div class="job-filters-bar mb-4">
        <div class="container">
            <form class="filter-form">
                <div class="search-inputs">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="keyword-search"
                            placeholder="Job title, keyword, or company">
                        <div id="suggestions-container" class="suggestions-container"></div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                        <input type="text" class="form-control" id="location-search"
                            placeholder="City, province, or postal code">
                    </div>
                    <button type="submit" class="btn btn-light">Find Jobs</button>
                </div>
                <div class="filter-pills" id="filter-pills">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle filter-pill" type="button" id="jobTypeDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Job Type
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="jobTypeDropdown">
                            <li><a class="dropdown-item" href="#" data-filter="job_type" data-value="">All Types</a>
                            </li>
                            <li><a class="dropdown-item" href="#" data-filter="job_type"
                                    data-value="full_time">Full-Time</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="job_type"
                                    data-value="part_time">Part-Time</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="job_type"
                                    data-value="contract">Contract</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="job_type" data-value="remote">Remote</a>
                            </li>
                            <li><a class="dropdown-item" href="#" data-filter="job_type"
                                    data-value="internship">Internship</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle filter-pill" type="button" id="salaryDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Salary
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="salaryDropdown">
                            <li><a class="dropdown-item" href="#" data-filter="salary_type" data-value="">Any Salary</a>
                            </li>
                            <li><a class="dropdown-item" href="#" data-filter="salary_type" data-value="salary">Annual
                                    Salary</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="salary_type" data-value="hourly">Hourly
                                    Rate</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle filter-pill" type="button" id="salaryRangeDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Salary Range
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="salaryRangeDropdown">
                            <li><a class="dropdown-item" href="#" data-filter="salary" data-value="">Any Range</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="salary" data-value="40000">> $40,000 / >
                                    $20/hr</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="salary" data-value="60000">> $60,000 / >
                                    $30/hr</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="salary" data-value="80000">> $80,000 / >
                                    $40/hr</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="salary" data-value="100000">> $100,000 /
                                    > $50/hr</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle filter-pill" type="button" id="radiusDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Distance
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="radiusDropdown">
                            <li><a class="dropdown-item" href="#" data-filter="radius" data-value="10">10 km</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="radius" data-value="25">25 km</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="radius" data-value="50">50 km</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="radius" data-value="100">100 km</a></li>
                            <li><a class="dropdown-item" href="#" data-filter="radius" data-value="">Any Distance</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Job Listing Page -->
    <div class="job-listing-page container-fluid mb-3">
        <!-- Job Listings Column -->
        <div class="job-listings-column">
            <div class="job-listings">
                <div class="p-3">
                    <h5 class="mb-0">Jobs in Canada</h5>
                    <small class="text-muted">1,250+ jobs</small>
                </div>
                <div id="job-listings-container">
                    <!-- Job cards will be injected here by JavaScript -->
                </div>
                <div id="pagination-container" class="py-3"></div>
            </div>
        </div>

        <!-- Job Detail Column -->
        <div class="job-detail-column">
            <div class="job-detail-view">
                <div id="job-detail-content">
                    <!-- Job details will be injected here by JavaScript -->
                    <div class="text-center p-5">
                        <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Select a job to see details</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script>
        const userType = "<?php echo isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'guest'; ?>";
        <?php
        $saved_jobs = [];
        if (isset($_SESSION['user_id'])) {
            if (!class_exists('Database')) {
                require_once 'php/Database.php';
            }
            if (!class_exists('User')) {
                require_once 'php/User.php';
            }
            $db = (new Database())->getConnection();
            $user_obj = new User($db);
            $saved_jobs = $user_obj->getSavedJobIds($_SESSION['user_id']);
        }
        ?>
        const savedJobs = <?php echo json_encode($saved_jobs); ?>;
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/time-ago.js"></script>
    <script src="js/jobs.js"></script>
</body>

</html>