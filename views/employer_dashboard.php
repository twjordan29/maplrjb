<?php
$isProfileComplete = $user_obj->checkIfEmployerProfileComplete($userId);
if (!$isProfileComplete):
    ?>
    <div class="alert alert-warning" role="alert">
        <strong>Your profile is incomplete!</strong> Please <a href="employer-profile.php" class="alert-link">finish your
            company profile</a> to post jobs and attract candidates.
    </div>
<?php endif; ?>

<div class="col-md-4 mb-4">
    <div class="dashboard-card">
        <h5 class="card-title"><i class="fas fa-inbox me-2"></i>Inbox</h5>
        <p class="card-text">View and manage your conversations.</p>
        <a href="inbox.php" class="btn btn-outline-primary mt-auto">Go to Inbox</a>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="dashboard-card">
            <h5 class="card-title"><i class="fas fa-plus-circle me-2"></i>Post a New Job</h5>
            <p class="card-text">Create a new listing to find the perfect candidate.</p>
            <a href="post-job.php" class="btn btn-primary mt-auto">Post Job</a>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="dashboard-card">
            <h5 class="card-title"><i class="fas fa-list-alt me-2"></i>Manage Listings</h5>
            <p class="card-text">View, edit, or remove your active job postings.</p>
            <a href="manage-jobs.php" class="btn btn-outline-primary mt-auto">Manage Jobs</a>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="dashboard-card">
            <h5 class="card-title"><i class="fas fa-building me-2"></i>Company Profile</h5>
            <p class="card-text">Keep your company information up to date.</p>
            <a href="employer-profile.php" class="btn btn-outline-primary mt-auto">Edit Profile</a>
        </div>
    </div>
</div>