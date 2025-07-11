<?php
session_start();
require_once 'php/Database.php';
require_once 'php/User.php';

// If the user is not logged in or is not an employer, redirect them.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header('Location: login.html');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Fetch existing employer profile data to pre-fill the form
$profileData = $user->getEmployerProfile($_SESSION['user_id']);
$isPremium = ($profileData['plan_type'] ?? 'free') === 'premium' || ($profileData['plan_type'] ?? 'free') === 'verified';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Profile - Maplr.ca</title>
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
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="my-3">Company Profile</h3>
                            </div>
                            <div class="card-body">
                                <div id="message-container"></div>
                                <form id="profileForm" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name"
                                            value="<?php echo htmlspecialchars($profileData['company_name'] ?? ''); ?>"
                                            required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="website" class="form-label">Website</label>
                                        <input type="url" class="form-control" id="website" name="website"
                                            value="<?php echo htmlspecialchars($profileData['website'] ?? ''); ?>"
                                            placeholder="https://example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Company Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="5"
                                            required><?php echo htmlspecialchars($profileData['description'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="work_for_us_desc" class="form-label">"Why Work For Us"
                                            Description</label>
                                        <textarea class="form-control" id="work_for_us_desc" name="work_for_us_desc"
                                            rows="5"><?php echo htmlspecialchars($profileData['work_for_us_desc'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Headquarters (e.g., Street
                                            Address)</label>
                                        <input type="text" class="form-control" id="location" name="location"
                                            value="<?php echo htmlspecialchars($profileData['location'] ?? ''); ?>">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                value="<?php echo htmlspecialchars($profileData['city'] ?? ''); ?>"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="province" class="form-label">Province</label>
                                            <select class="form-select" id="province" name="province" required>
                                                <option value="">Select Province</option>
                                                <?php
                                                require 'includes/provinces.php';
                                                foreach ($provinces as $abbr => $name) {
                                                    $selected = ($profileData['province'] == $name) ? 'selected' : '';
                                                    echo "<option value=\"$name\" $selected>$name</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <?php if ($isPremium): ?>
                                        <hr>
                                        <h5 class="mb-3">Premium Features</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Company Logo</label>
                                                <div class="image-upload-wrapper">
                                                    <input class="image-upload-input" type="file" id="logo" name="logo"
                                                        accept="image/*">
                                                    <div class="image-upload-display">
                                                        <?php if (!empty($profileData['logo'])): ?>
                                                            <img src="<?php echo htmlspecialchars($profileData['logo']); ?>"
                                                                alt="Company Logo">
                                                        <?php else: ?>
                                                            <div class="upload-placeholder">
                                                                <i class="fas fa-camera"></i>
                                                                <p>Click to upload logo</p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Profile Header Image</label>
                                                <div class="image-upload-wrapper header-upload-wrapper">
                                                    <input class="image-upload-input" type="file" id="header" name="header"
                                                        accept="image/*">
                                                    <div class="image-upload-display">
                                                        <?php if (!empty($profileData['header_image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($profileData['header_image']); ?>"
                                                                alt="Header Image">
                                                        <?php else: ?>
                                                            <div class="upload-placeholder">
                                                                <i class="fas fa-camera"></i>
                                                                <p>Click to upload header</p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="video_url" class="form-label">Company Video URL</label>
                                            <input type="url" class="form-control" id="video_url" name="video_url"
                                                value="<?php echo htmlspecialchars($profileData['video_url'] ?? ''); ?>"
                                                placeholder="https://youtube.com/watch?v=...">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Social Media Links</label>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                                                <input type="url" class="form-control" name="social_links[linkedin]"
                                                    placeholder="LinkedIn Profile URL"
                                                    value="<?php echo htmlspecialchars($profileData['social_links']['linkedin'] ?? ''); ?>">
                                            </div>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                                <input type="url" class="form-control" name="social_links[twitter]"
                                                    placeholder="Twitter Profile URL"
                                                    value="<?php echo htmlspecialchars($profileData['social_links']['twitter'] ?? ''); ?>">
                                            </div>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                                                <input type="url" class="form-control" name="social_links[facebook]"
                                                    placeholder="Facebook Profile URL"
                                                    value="<?php echo htmlspecialchars($profileData['social_links']['facebook'] ?? ''); ?>">
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                                <input type="url" class="form-control" name="social_links[instagram]"
                                                    placeholder="Instagram Profile URL"
                                                    value="<?php echo htmlspecialchars($profileData['social_links']['instagram'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="brand_color" class="form-label">Brand Color</label>
                                            <input type="color" class="form-control form-control-color" id="brand_color"
                                                name="brand_color"
                                                value="<?php echo htmlspecialchars($profileData['brand_color'] ?? '#ffffff'); ?>"
                                                title="Choose your brand color">
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-primary mt-3">Save Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/employer-profile.js"></script>
    <script src="js/dashboard.js"></script>
</body>

</html>