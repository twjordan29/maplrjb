<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';

// If the user is not logged in or is not a job seeker, redirect them.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header('Location: login.html');
    exit();
}

$db = (new Database())->getConnection();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Maplr.ca</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="dashboard-content">
            <h1 class="h3 mb-4">My Applications</h1>
            <div class="card">
                <div class="card-body">
                    <div id="applications-container">
                        <!-- Applications will be loaded here by JavaScript -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/my-applications.js"></script>
</body>

</html>