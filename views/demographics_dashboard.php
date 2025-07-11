<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: ../login.php");
    exit();
}

require_once '../php/Database.php';
require_once '../php/User.php';

$db = (new Database())->getConnection();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demographics Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/sidebar.php'; ?>
        <div class="dashboard-content">
            <h1>Applicant Demographics</h1>
            <p>This dashboard shows demographic information about the applicants for your jobs.</p>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Applicants by Location</h5>
                            <canvas id="locationChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Applicants by Education Level</h5>
                            <canvas id="educationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Applicants by Age Range</h5>
                            <canvas id="ageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('../api/get_demographics_data.php')
                .then(response => response.json())
                .then(data => {
                    // Location Chart
                    const locationCtx = document.getElementById('locationChart').getContext('2d');
                    if (data.location.length > 0) {
                        new Chart(locationCtx, {
                            type: 'pie',
                            data: {
                                labels: data.location.map(item => item.province),
                                datasets: [{
                                    data: data.location.map(item => item.count),
                                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                                }]
                            }
                        });
                    } else {
                        document.getElementById('locationChart').parentElement.innerHTML += '<p class="text-muted">No location data available.</p>';
                    }

                    // Education Chart
                    const educationCtx = document.getElementById('educationChart').getContext('2d');
                    if (data.education.length > 0) {
                        new Chart(educationCtx, {
                            type: 'doughnut',
                            data: {
                                labels: data.education.map(item => item.education_level),
                                datasets: [{
                                    data: data.education.map(item => item.count),
                                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                                }]
                            }
                        });
                    } else {
                        document.getElementById('educationChart').parentElement.innerHTML += '<p class="text-muted">No education data available.</p>';
                    }

                    // Age Chart
                    const ageCtx = document.getElementById('ageChart').getContext('2d');
                    if (data.age.length > 0) {
                        new Chart(ageCtx, {
                            type: 'bar',
                            data: {
                                labels: data.age.map(item => item.age_range),
                                datasets: [{
                                    label: 'Number of Applicants',
                                    data: data.age.map(item => item.count),
                                    backgroundColor: '#36A2EB'
                                }]
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
                    } else {
                        document.getElementById('ageChart').parentElement.innerHTML += '<p class="text-muted">No age data available.</p>';
                    }
                });
        });
    </script>
</body>

</html>