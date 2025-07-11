<?php
session_start();

// Check if the user is logged in and is a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    // Redirect to login page or an error page
    header('Location: login.html');
    exit;
}
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
        <div class="dashboard-content">
            <h1>My Profile</h1>
            <p>Keep your profile up to date to attract the best employers.</p>

            <form id="profile-form">
                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Basic Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="headline" class="form-label">Headline</label>
                            <input type="text" class="form-control" id="headline" name="headline" maxlength="100">
                            <div id="headline-counter" class="form-text text-end">100/100</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="location_city" name="location_city">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location_province" class="form-label">Province</label>
                            <select class="form-select" id="location_province" name="location_province">
                                <option value="">Select Province</option>
                                <?php
                                include 'includes/provinces.php';
                                foreach ($provinces as $abbr => $name) {
                                    echo "<option value=\"$abbr\">$name</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Availability</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="availability[]" value="weekends"
                                    id="avail-weekends">
                                <label class="form-check-label" for="avail-weekends">Weekends</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="availability[]" value="weekdays"
                                    id="avail-weekdays">
                                <label class="form-check-label" for="avail-weekdays">Monday to Friday</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="availability[]" value="days"
                                    id="avail-days">
                                <label class="form-check-label" for="avail-days">Days</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="availability[]" value="evenings"
                                    id="avail-evenings">
                                <label class="form-check-label" for="avail-evenings">Evenings</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="availability[]" value="nights"
                                    id="avail-nights">
                                <label class="form-check-label" for="avail-nights">Nights</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="summary" class="form-label">Summary</label>
                            <textarea class="form-control" id="summary" name="summary" rows="5"></textarea>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Professional Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="experience_years" class="form-label">Years of Experience</label>
                            <input type="number" class="form-control" id="experience_years" name="experience_years">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="education_level" class="form-label">Education Level</label>
                            <select class="form-select" id="education_level" name="education_level">
                                <option value="" disabled selected>Select...</option>
                                <option value="High School Diploma">High School Diploma</option>
                                <option value="College Diploma">College Diploma</option>
                                <option value="Trade Certification">Trade Certification</option>
                                <option value="Bachelor's Degree">Bachelor's Degree</option>
                                <option value="Master's Degree">Master's Degree</option>
                                <option value="Doctorate / PhD">Doctorate / PhD</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="salary_type" class="form-label">Salary Type</label>
                            <select class="form-select" id="salary_type" name="salary_type">
                                <option value="annual">Annual</option>
                                <option value="hourly">Hourly</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="desired_salary_min" class="form-label">Desired Salary (Minimum)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="desired_salary_min"
                                    name="desired_salary_min">
                                <span class="input-group-text" id="salary_suffix_min">/yr</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="desired_salary_max" class="form-label">Desired Salary (Maximum)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="desired_salary_max"
                                    name="desired_salary_max">
                                <span class="input-group-text" id="salary_suffix_max">/yr</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Skills</h5>
                    <p>Add skills that are relevant to your desired job.</p>
                    <div id="skills-container"></div>
                    <div class="input-group mt-2 position-relative">
                        <input type="text" id="skill-input" class="form-control"
                            placeholder="e.g., JavaScript, Project Management">
                        <button class="btn btn-outline-secondary" type="button" id="add-skill-btn">Add Skill</button>
                        <div id="skill-suggestions-container" class="suggestions-container"></div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Work Experience</h5>
                    <div id="experience-container"></div>
                    <button class="btn btn-outline-secondary mt-2" type="button" id="add-experience-btn">Add
                        Experience</button>
                </div>

                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Education</h5>
                    <div id="education-container"></div>
                    <button class="btn btn-outline-secondary mt-2" type="button" id="add-education-btn">Add
                        Education</button>
                </div>

                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Online Presence</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                            <input type="url" class="form-control" id="linkedin_url" name="linkedin_url">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="portfolio_url" class="form-label">Portfolio/Website URL</label>
                            <input type="url" class="form-control" id="portfolio_url" name="portfolio_url">
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mb-4">
                    <h5 class="card-title">Settings</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_remote" name="is_remote">
                        <label class="form-check-label" for="is_remote">Open to Remote Work</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_searchable"
                            name="is_searchable" checked>
                        <label class="form-check-label" for="is_searchable">Allow employers to find my profile</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Profile</button>
            </form>

            <template id="experience-template">
                <div class="experience-item mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control" name="exp_job_title[]">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" name="exp_company[]">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="exp_start_date[]">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="exp_end_date[]">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="exp_description[]" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
                </div>
            </template>

            <template id="education-template">
                <div class="education-item mb-3 p-3 border rounded">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">School</label>
                            <input type="text" class="form-control" name="edu_school[]">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Degree</label>
                            <input type="text" class="form-control" name="edu_degree[]">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field of Study</label>
                            <input type="text" class="form-control" name="edu_field_of_study[]">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start Year</label>
                            <input type="number" class="form-control" name="edu_start_year[]">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End Year</label>
                            <input type="number" class="form-control" name="edu_end_year[]">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn">Remove</button>
                </div>
            </template>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/candidate-profile.js"></script>
</body>

</html>