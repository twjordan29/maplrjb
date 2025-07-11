<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header('Location: login.php');
    exit();
}

$db = (new Database())->getConnection();
$user_obj = new User($db);
$user = $user_obj->getById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Maplr.ca</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="dashboard-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">My Profile</h1>
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'https://via.placeholder.com/150'); ?>"
                                alt="Profile Picture" class="rounded-circle img-fluid" style="width: 150px;">
                            <h5 class="my-3">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </h5>
                            <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Current Resume</h5>
                            <?php if (!empty($user['resume_path'])): ?>
                                <a href="<?php echo htmlspecialchars($user['resume_path']); ?>" target="_blank"
                                    class="btn btn-outline-primary">View Resume</a>
                            <?php else: ?>
                                <p class="text-muted">No resume uploaded.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div id="message-container"></div>
                            <form id="profileForm" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                            value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city"
                                            value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="province" class="form-label">Province</label>
                                        <select class="form-select" id="province" name="province">
                                            <option value="">Select Province</option>
                                            <?php
                                            require 'includes/provinces.php';
                                            foreach ($provinces as $abbr => $name) {
                                                $selected = ($user['province'] == $name) ? 'selected' : '';
                                                echo "<option value=\"$name\" $selected>$name</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                        value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="education_level" class="form-label">Education Level</label>
                                    <select class="form-select" id="education_level" name="education_level">
                                        <option value="" <?php echo empty($user['education_level']) ? 'selected' : ''; ?>>Select...</option>
                                        <option value="High School" <?php echo ($user['education_level'] ?? '') == 'High School' ? 'selected' : ''; ?>>High School</option>
                                        <option value="Diploma" <?php echo ($user['education_level'] ?? '') == 'Diploma' ? 'selected' : ''; ?>>Diploma</option>
                                        <option value="Bachelors" <?php echo ($user['education_level'] ?? '') == 'Bachelors' ? 'selected' : ''; ?>>Bachelor's Degree</option>
                                        <option value="Masters" <?php echo ($user['education_level'] ?? '') == 'Masters' ? 'selected' : ''; ?>>Master's Degree</option>
                                        <option value="PhD" <?php echo ($user['education_level'] ?? '') == 'PhD' ? 'selected' : ''; ?>>PhD</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture"
                                        accept="image/*">
                                </div>
                                <div class="mb-3">
                                    <label for="resume" class="form-label">Upload New Resume</label>
                                    <input type="file" class="form-control" id="resume" name="resume"
                                        accept=".pdf,.doc,.docx">
                                    <div class="form-text">If you don't upload a new resume, your existing one will be
                                        kept.</div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/job-seeker-profile.js"></script>
</body>

</html>