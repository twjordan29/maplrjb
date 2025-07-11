<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

require_once 'php/Database.php';
require_once 'php/User.php';

$db = (new Database())->getConnection();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Performance Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="dashboard-content">
            <h1>Job Performance Dashboard</h1>
            <p>This dashboard shows how your job postings are performing.</p>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Job Performance Overview</h5>
                            <div class="chart-container">
                                <canvas id="jobPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('api/get_job_analytics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        document.querySelector('.card-body').innerHTML += '<p class="text-muted">No performance data available yet. Share your jobs to get started!</p>';
                        return;
                    }

                    const labels = data.map(item => item.job_title);
                    const viewsData = data.map(item => item.views);
                    const clicksData = data.map(item => item.clicks);
                    const applicationsData = data.map(item => item.applications);

                    const ctx = document.getElementById('jobPerformanceChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Views',
                                    data: viewsData,
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Clicks on "Apply"',
                                    data: clicksData,
                                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                                    borderColor: 'rgba(255, 206, 86, 1)',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Applications',
                                    data: applicationsData,
                                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                });
        });
    </script>
    <script>
        document.querySelector('.sidebar-toggle').addEventListener('click', function () {
            document.querySelector('.dashboard-sidebar').classList.toggle('show');
        });
    </script>
</body>

</html>