<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_obj = new User($db);

// Refresh user data from DB
$user_data = $user_obj->getById($_SESSION['user_id']);
if ($user_data) {
    $_SESSION['user_name'] = $user_data['first_name'] . ' ' . $user_data['last_name'];
    $_SESSION['user_type'] = $user_data['user_type'];
}

$userName = $_SESSION['user_name'];
$userType = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];

if ($userType == 'employer') {
    $employer_profile = $user_obj->getEmployerProfile($userId);
    if ($employer_profile && !empty($employer_profile['company_name'])) {
        $userName = $employer_profile['company_name'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Maplr.ca</title>
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

        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Dashboard</h1>
                <span>Welcome, <?php echo htmlspecialchars($userName); ?>!</span>
            </div>

            <?php
            // Include the correct dashboard view based on user type
            if ($userType == 'employer') {
                include 'views/employer_dashboard.php';
            } else {
                include 'views/job_seeker_dashboard.php';
            }
            ?>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>