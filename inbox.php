<?php
session_start();
require_once 'php/Database.php';

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - Maplr.ca</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/inbox.css">
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <nav class="dashboard-sidebar">
            <div class="text-center mb-4">
                <a class="navbar-brand" href="index.html">
                    <i class="fas fa-maple-leaf maple-leaf"></i> Maplr.ca
                </a>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="inbox.php">
                        <i class="fas fa-inbox"></i> Inbox
                    </a>
                </li>
                <?php if ($_SESSION['user_type'] == 'employer'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="employer-profile.php">
                            <i class="fas fa-building"></i> Company Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post-job.php">
                            <i class="fas fa-plus-circle"></i> Post a Job
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-jobs.php">
                            <i class="fas fa-list-alt"></i> Manage Jobs
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-circle"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-bookmark"></i> Saved Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
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

        <!-- Main Content -->
        <div class="inbox-container">
            <div class="conversations-list">
                <div class="inbox-header">
                    <h4>Inbox</h4>
                </div>
                <div id="conversations-container">
                    <!-- Conversations will be loaded here by JavaScript -->
                </div>
            </div>
            <div class="message-view">
                <div id="message-view-content">
                    <div class="text-center p-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Select a conversation to start messaging</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/inbox.js"></script>
</body>

</html>