<?php
session_start();
require_once 'php/Database.php';
require_once 'php/Jobs.php';
require_once 'php/User.php';

// If the user is not logged in, redirect them.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

if ($_SESSION['user_type'] === 'employer') {
    header('Location: dashboard.php');
    exit();
}

$db = (new Database())->getConnection();
$jobs = new Jobs($db);
$user_obj = new User($db);

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$job = $jobs->getJobById($job_id);
$user = $user_obj->getById($_SESSION['user_id']);
$questions = $jobs->getQuestionsByJobId($job_id);

if (!$job) {
    // Redirect or show an error if the job doesn't exist
    header('Location: jobs.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?php echo htmlspecialchars($job['title']); ?> - Maplr.ca</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-maple-leaf maple-leaf"></i> Maplr.ca
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="my-3">Apply for <?php echo htmlspecialchars($job['title']); ?></h3>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="50"
                                aria-valuemin="0" aria-valuemax="100">Step 1 of 2</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="message-container"></div>
                        <form id="applyJobForm" enctype="multipart/form-data">
                            <div id="step1">
                                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="cover_letter" class="form-label">Cover Letter</label>
                                    <textarea class="form-control" id="cover_letter" name="cover_letter" rows="8"
                                        placeholder="Write a brief message to the employer..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="resume" class="form-label">Upload Your Resume</label>
                                    <input type="file" class="form-control" id="resume" name="resume"
                                        accept=".pdf,.doc,.docx" required>
                                    <div class="form-text">Accepted file types: PDF, DOC, DOCX. Max size: 5MB.</div>
                                </div>
                                <button type="button" class="btn btn-primary w-100 mt-3"
                                    onclick="nextStep()">Next</button>
                            </div>
                            <div id="step2" style="display: none;">
                                <?php if (!empty($questions)): ?>
                                    <h5>Screening Questions</h5>
                                    <?php foreach ($questions as $question): ?>
                                        <div class="mb-3">
                                            <label for="question_<?php echo $question['id']; ?>"
                                                class="form-label"><?php echo htmlspecialchars($question['question_text']); ?></label>
                                            <input type="text" class="form-control" id="question_<?php echo $question['id']; ?>"
                                                name="answers[<?php echo $question['id']; ?>]" required>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No screening questions for this job.</p>
                                <?php endif; ?>
                                <button type="button" class="btn btn-secondary mt-3" onclick="prevStep()">Back</button>
                                <button type="submit" class="btn btn-primary mt-3">Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($job['company_name'] ?? 'N/A'); ?>
                        </p>
                        <hr>
                        <p><i
                                class="fas fa-map-marker-alt me-2 text-muted"></i><?php echo htmlspecialchars($job['city']); ?>,
                            <?php echo htmlspecialchars($job['province']); ?>
                        </p>
                        <p><i
                                class="fas fa-briefcase me-2 text-muted"></i><?php echo htmlspecialchars(ucfirst(str_replace('_', '-', $job['job_type']))); ?>
                        </p>
                        <p><i class="fas fa-dollar-sign me-2 text-muted"></i>Salary information not provided</p>
                        <a href="jobs.php" class="btn btn-outline-secondary w-100 mt-2">Back to Jobs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/apply-now.js"></script>
    <script>
        function nextStep() {
            let isValid = true;
            const step1Inputs = document.querySelectorAll('#step1 [required]');
            step1Inputs.forEach(input => {
                if (!input.value) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (isValid) {
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
                const progressBar = document.querySelector('.progress-bar');
                progressBar.style.width = '100%';
                progressBar.textContent = 'Step 2 of 2';
            }
        }

        function prevStep() {
            document.getElementById('step1').style.display = 'block';
            document.getElementById('step2').style.display = 'none';
            const progressBar = document.querySelector('.progress-bar');
            progressBar.style.width = '50%';
            progressBar.textContent = 'Step 1 of 2';
        }
    </script>
</body>

</html>