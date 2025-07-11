<nav class="dashboard-sidebar">
    <div class="text-center mb-4">
        <a class="navbar-brand" href="index.html">
            <i class="fas fa-maple-leaf maple-leaf"></i> Maplr.ca
        </a>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"
                href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <?php if ($_SESSION['user_type'] == 'employer'):
            // We need to check for premium status to show certain links
            $user_obj_sidebar = new User($db);
            $employer_profile_sidebar = $user_obj_sidebar->getEmployerProfile($_SESSION['user_id']);
            $is_premium_sidebar = ($employer_profile_sidebar['plan_type'] ?? 'free') === 'premium' || ($employer_profile_sidebar['plan_type'] ?? 'free') === 'verified';
            ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'employer-profile.php' ? 'active' : ''; ?>"
                    href="employer-profile.php">
                    <i class="fas fa-building"></i> Company Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'post-job.php' ? 'active' : ''; ?>"
                    href="post-job.php">
                    <i class="fas fa-plus-circle"></i> Post a Job
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage-jobs.php' ? 'active' : ''; ?>"
                    href="manage-jobs.php">
                    <i class="fas fa-list-alt"></i> Manage Jobs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage-reviews.php' ? 'active' : ''; ?>"
                    href="manage-reviews.php">
                    <i class="fas fa-star"></i> Manage Reviews
                </a>
            </li>
            <hr>
            <?php if ($is_premium_sidebar): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'candidates.php' ? 'active' : ''; ?>"
                        href="candidates.php">
                        <i class="fas fa-search"></i> Find Candidates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'job_performance_dashboard.php' ? 'active' : ''; ?>"
                        href="job_performance_dashboard.php">
                        <i class="fas fa-chart-line"></i> Job Performance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'demographics_dashboard.php' ? 'active' : ''; ?>"
                        href="demographics_dashboard.php">
                        <i class="fas fa-users-cog"></i> Demographics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'interviews.php' ? 'active' : ''; ?>"
                        href="interviews.php">
                        <i class="fas fa-calendar-alt"></i> Scheduled Interviews
                    </a>
                </li>
            <?php endif; ?>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'job-seeker-profile.php' ? 'active' : ''; ?>"
                    href="job-seeker-profile.php">
                    <i class="fas fa-user-circle"></i> My Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-bookmark"></i> Saved Jobs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my-interviews.php' ? 'active' : ''; ?>"
                    href="my-interviews.php">
                    <i class="fas fa-calendar-check"></i> My Interviews
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my-applications.php' ? 'active' : ''; ?>"
                    href="my-applications.php">
                    <i class="fas fa-file-alt"></i> My Applications
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item mt-auto">
            <a class="nav-link" href="api/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</nav>