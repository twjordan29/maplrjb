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

$profileData = $user->getEmployerProfile($_SESSION['user_id']);
$isPremium = ($profileData['plan_type'] ?? 'free') === 'premium' || ($profileData['plan_type'] ?? 'free') === 'verified';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$reviews_per_page = 10;
$offset = ($page - 1) * $reviews_per_page;

$total_reviews_stmt = $db->prepare("SELECT COUNT(*) as total FROM reviews WHERE employer_user_id = ?");
$total_reviews_stmt->bind_param("i", $_SESSION['user_id']);
$total_reviews_stmt->execute();
$total_reviews_result = $total_reviews_stmt->get_result();
$total_reviews = $total_reviews_result->fetch_assoc()['total'];
$total_pages = ceil($total_reviews / $reviews_per_page);

$stmt = $db->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.job_seeker_user_id = u.id WHERE r.employer_user_id = ? ORDER BY r.created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $_SESSION['user_id'], $reviews_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$reviews = [];
while ($row = $result->fetch_assoc()) {
    if ($row['is_anonymous']) {
        $row['first_name'] = 'Anonymous';
        $row['last_name'] = '';
    } else {
        $row['last_name'] = substr($row['last_name'], 0, 1) . '.';
    }
    $reviews[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Maplr.ca</title>
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="my-3">Manage Reviews</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reviews)): ?>
                            <p>You have no reviews yet.</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review mb-4 border-bottom pb-3">
                                    <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                    - <span
                                        class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></span>
                                    <h5><?php echo htmlspecialchars($review['review_title']); ?></h5>
                                    <p>Rating: <?php echo htmlspecialchars($review['rating']); ?></p>
                                    <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>

                                    <?php if ($review['employer_response']): ?>
                                        <div class="employer-response p-2 ms-4 bg-light rounded">
                                            <strong>Your Response:</strong>
                                            <p><?php echo nl2br(htmlspecialchars($review['employer_response'])); ?></p>
                                        </div>
                                    <?php elseif ($isPremium): ?>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#responseModal-<?php echo $review['id']; ?>">Respond</button>

                                        <!-- Modal -->
                                        <div class="modal fade" id="responseModal-<?php echo $review['id']; ?>" tabindex="-1"
                                            aria-labelledby="responseModalLabel-<?php echo $review['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="responseModalLabel-<?php echo $review['id']; ?>">Respond to Review
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form class="response-form" data-review-id="<?php echo $review['id']; ?>">
                                                            <div class="mb-3">
                                                                <label for="response_text-<?php echo $review['id']; ?>"
                                                                    class="form-label">Your Response</label>
                                                                <textarea class="form-control"
                                                                    id="response_text-<?php echo $review['id']; ?>" rows="5"
                                                                    required></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Submit Response</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <nav aria-label="Page navigation">
                            <?php
                            require_once 'includes/pagination.php';
                            echo generate_pagination($total_pages, $page);
                            ?>
                        </nav>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
        <div id="responseToast" class="toast align-items-center text-white bg-maplr-red-dark border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Response submitted successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script>
        const toastEl = document.getElementById('responseToast');
        const toast = new bootstrap.Toast(toastEl);

        document.querySelectorAll('.response-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const reviewId = this.dataset.reviewId;
                const responseText = this.querySelector('textarea').value;
                const reviewContainer = this.closest('.review');

                fetch('api/respond_to_review.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        review_id: reviewId,
                        response_text: responseText
                    })
                })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error('Network response was not ok.');
                    })
                    .then(data => {
                        toast.show();
                        const modalEl = document.getElementById(`responseModal-${reviewId}`);
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        const responseHtml = `
                        <div class="employer-response p-2 ms-4 bg-light rounded">
                            <strong>Your Response:</strong>
                            <p>${responseText.replace(/\n/g, '<br>')}</p>
                        </div>
                    `;

                        const respondButton = reviewContainer.querySelector('button[data-bs-target]');
                        if (respondButton) {
                            respondButton.remove();
                        }
                        reviewContainer.insertAdjacentHTML('beforeend', responseHtml);

                    })
                    .catch(error => {
                        console.error('There has been a problem with your fetch operation:', error);
                        alert('Failed to submit response.');
                    });
            });
        });
    </script>
</body>

</html>