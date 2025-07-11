<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';
require_once 'php/Jobs.php';

// If the user is not logged in or is not an employer, redirect them.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$jobs = new Jobs($db);
$my_jobs = $jobs->getJobsByEmployer($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Maplr.ca</title>
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
            <h1 class="h3 mb-4">Manage Your Job Postings</h1>
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Your Job Postings</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Posted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($job = $my_jobs->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($job['title']); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?></td>
                                        <td><?php echo htmlspecialchars($job['city']); ?>,
                                            <?php echo htmlspecialchars($job['province']); ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                        <td><span
                                                class="badge bg-<?php echo $job['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?></span>
                                        </td>
                                        <td>
                                            <a href="edit-job.php?id=<?php echo $job['id']; ?>"
                                                class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="view-applicants.php?job_id=<?php echo $job['id']; ?>"
                                                class="btn btn-sm btn-info"><i class="fas fa-users"></i> Applicants</a>
                                            <a href="#" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i>
                                                Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>