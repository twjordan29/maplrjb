document.addEventListener('DOMContentLoaded', function () {
    const companyId = new URLSearchParams(window.location.search).get('id');
    const container = document.getElementById('company-profile-container');

    if (companyId) {
        fetch(`api/get_company_profile.php?id=${companyId}`)
            .then(response => response.json())
            .then(data => {
                if (data.profile) {
                    const { profile, jobs } = data;
                    document.title = `${profile.company_name} - Maplr.ca`;

                    let jobsHtml = '';
                    if (jobs.length > 0) {
                        jobs.forEach(job => {
                            jobsHtml += `
                                <div class="job-card-listing">
                                    <a href="jobs.php#${job.id}" class="text-decoration-none text-dark">
                                        <h5>${job.title}</h5>
                                        <p class="text-muted">${job.city}, ${job.province}</p>
                                    </a>
                                </div>
                            `;
                        });
                    } else {
                        jobsHtml = '<p>No active job postings.</p>';
                    }

                    let logoHtml = '';
                    if (profile.logo) {
                        logoHtml = `<img src="${profile.logo}" alt="${profile.company_name} Logo" class="company-profile-logo">`;
                    } else {
                        logoHtml = `<div class="company-logo-text company-profile-logo">${profile.company_name.substr(0, 2).toUpperCase()}</div>`;
                    }

                    let verifiedBadge = profile.plan_type === 'verified' ? '<img src="images/maple.png" class="verified-icon" alt="Verified Employer" title="Verified Employer" data-bs-toggle="tooltip" data-bs-placement="top">' : '';
                    let videoHtml = '';
                    if (profile.video_url) {
                        videoHtml = `
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h3 class="card-title">Company Video</h3>
                                    <div class="ratio ratio-16x9">
                                        <iframe src="${profile.video_url.replace('watch?v=', 'embed/')}" title="Company Video" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    let socialLinksHtml = '';
                    if (profile.social_links) {
                        const links = JSON.parse(profile.social_links);
                        let socialItems = '';
                        if (links.linkedin) socialItems += `<a href="${links.linkedin}" target="_blank" class="btn btn-outline-primary me-2"><i class="fab fa-linkedin"></i></a>`;
                        if (links.twitter) socialItems += `<a href="${links.twitter}" target="_blank" class="btn btn-outline-primary me-2"><i class="fab fa-twitter"></i></a>`;
                        if (links.facebook) socialItems += `<a href="${links.facebook}" target="_blank" class="btn btn-outline-primary me-2"><i class="fab fa-facebook"></i></a>`;
                        if (links.instagram) socialItems += `<a href="${links.instagram}" target="_blank" class="btn btn-outline-primary"><i class="fab fa-instagram"></i></a>`;

                        if (socialItems) {
                            socialLinksHtml = `
                                <h3 class="card-title mb-3 mt-5">Follow Us</h3>
                                ${socialItems}
                            `;
                        }
                    }

                    let reviewFormHtml = '';
                    if (isJobSeeker) {
                        reviewFormHtml = `
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h5>Leave a Review</h5>
                                    <form id="reviewForm">
                                        <input type="hidden" name="employer_user_id" value="${profile.user_id}">
                                        <div class="mb-3">
                                            <label for="rating" class="form-label">Rating (0.0 - 5.0)</label>
                                            <input type="number" class="form-control" id="rating" name="rating" step="0.1" min="1" max="5" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="review_title" class="form-label">Review Title</label>
                                            <input type="text" class="form-control" id="review_title" name="review_title" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="review_text" class="form-label">Review</label>
                                            <textarea class="form-control" id="review_text" name="review_text" rows="3" required></textarea>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous">
                                            <label class="form-check-label" for="is_anonymous">
                                                Submit anonymously
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit Review</button>
                                    </form>
                                </div>
                            </div>
                        `;
                    }

                    container.innerHTML = `
                        <div class="company-profile-header" style="background-image: url('${profile.header_image}'); background-color: ${profile.brand_color};">
                            <div class="container">
                                ${logoHtml}
                                <h1 class="company-profile-name">${profile.company_name} ${verifiedBadge}</h1>
                            </div>
                        </div>
                        <div class="container py-4">
                            <div class="row">
                                <div class="col-lg-8">
                                    <ul class="nav nav-tabs modern-tabs" id="companyProfileTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="why-work-for-us-tab" data-bs-toggle="tab" data-bs-target="#why-work-for-us" type="button" role="tab" aria-controls="why-work-for-us" aria-selected="false">Why Work For Us</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="jobs-tab" data-bs-toggle="tab" data-bs-target="#jobs" type="button" role="tab" aria-controls="jobs" aria-selected="false">Jobs</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button" role="tab" aria-controls="questions" aria-selected="false">Questions</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Reviews <span id="average-rating" class="badge bg-secondary ms-1"></span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="companyProfileTabsContent">
                                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                                            <div class="card card-tab-content">
                                                <div class="card-body">
                                                    <h3 class="card-title">About ${profile.company_name}</h3>
                                                    <p>${profile.description}</p>
                                                    ${videoHtml}
                                                    ${socialLinksHtml}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="why-work-for-us" role="tabpanel" aria-labelledby="why-work-for-us-tab">
                                            <div class="card card-tab-content"><div class="card-body">${profile.work_for_us_desc ? profile.work_for_us_desc.replace(/\n/g, '<br>') : '<p>No description provided.</p>'}</div></div>
                                        </div>
                                        <div class="tab-pane fade" id="jobs" role="tabpanel" aria-labelledby="jobs-tab">
                                            <div class="card card-tab-content"><div class="card-body">${jobsHtml}</div></div>
                                        </div>
                                        <div class="tab-pane fade" id="questions" role="tabpanel" aria-labelledby="questions-tab">
                                            <div class="card card-tab-content"><div class="card-body"><p>Coming soon.</p></div></div>
                                        </div>
                                        <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                                            <div class="card card-tab-content">
                                                <div class="card-body">
                                                    <div id="reviews-container"></div>
                                                    <nav id="reviews-pagination" aria-label="Reviews navigation"></nav>
                                                </div>
                                            </div>
                                            ${reviewFormHtml}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Head Office</h5>
                                            <p><i class="fas fa-map-marker-alt me-2"></i>${profile.location}, ${profile.city}, ${profile.province}</p>
                                            <a href="${profile.website}" target="_blank" class="btn btn-outline-primary btn-sm">Visit Website</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })

                    function fetchReviews(page = 1) {
                        fetch(`api/get_reviews.php?employer_id=${companyId}&page=${page}`)
                            .then(response => response.json())
                            .then(reviewData => {
                                const { reviews, average_rating, total_pages, current_page } = reviewData;
                                document.getElementById('average-rating').textContent = average_rating || 'N/A';
                                const reviewsContainer = document.getElementById('reviews-container');
                                if (reviews.length > 0) {
                                    let reviewsHtml = '';
                                    reviews.forEach(review => {
                                        reviewsHtml += `
                                            <div class="review mb-3 border-bottom pb-3">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1">${review.review_title}</h5>
                                                    <small>${new Date(review.created_at).toLocaleDateString()}</small>
                                                </div>
                                                <p class="mb-1">Rating: ${review.rating}</p>
                                                <small>${review.first_name} ${review.last_name}</small>
                                                <p class="mt-2">${review.review_text}</p>
                                                ${review.employer_response ? `<div class="employer-response p-2 ms-4 bg-light rounded"><strong class="mb-3">Employer Response:</strong><div>${review.employer_response}</div></div>` : ''}
                                            </div>
                                        `;
                                    });
                                    reviewsContainer.innerHTML = reviewsHtml;
                                } else {
                                    reviewsContainer.innerHTML = '<p>No reviews yet.</p>';
                                }
                                renderPagination(total_pages, current_page);
                            });
                    }

                    function renderPagination(totalPages, currentPage) {
                        const paginationContainer = document.getElementById('reviews-pagination');
                        if (totalPages <= 1) {
                            paginationContainer.innerHTML = '';
                            return;
                        }

                        let paginationHtml = '<ul class="pagination justify-content-center">';
                        const window = 2;
                        currentPage = parseInt(currentPage);

                        if (currentPage > 1) {
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">First</a></li>`;
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
                        }

                        if (currentPage > window + 1) {
                            paginationHtml += '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }

                        for (let i = Math.max(1, currentPage - window); i <= Math.min(totalPages, currentPage + window); i++) {
                            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }

                        if (currentPage < totalPages - window) {
                            paginationHtml += '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                        }

                        if (currentPage < totalPages) {
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
                            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">Last</a></li>`;
                        }

                        paginationHtml += '</ul>';
                        paginationContainer.innerHTML = paginationHtml;

                        document.querySelectorAll('#reviews-pagination a').forEach(link => {
                            link.addEventListener('click', function (e) {
                                e.preventDefault();
                                const page = this.dataset.page;
                                fetchReviews(page);
                            });
                        });
                    }

                    fetchReviews();

                    if (isJobSeeker) {
                        document.getElementById('reviewForm').addEventListener('submit', function (e) {
                            e.preventDefault();
                            const formData = new FormData(this);
                            const data = Object.fromEntries(formData.entries());

                            fetch('api/submit_review.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(data)
                            })
                                .then(res => {
                                    if (res.ok) {
                                        return res.json();
                                    }
                                    throw new Error('Network response was not ok.');
                                })
                                .then(result => {
                                    const toastEl = document.getElementById('reviewToast');
                                    const toast = new bootstrap.Toast(toastEl);
                                    toast.show();
                                    document.getElementById('reviewForm').reset();
                                    fetchReviews();
                                })
                                .catch(error => {
                                    console.error('There has been a problem with your fetch operation:', error);
                                    alert('Failed to submit review.');
                                });
                        });
                    }
                } else {
                    container.innerHTML = '<div class="container py-5"><h1 class="text-center">Company not found.</h1></div>';
                }
            });
    } else {
        container.innerHTML = '<div class="container py-5"><h1 class="text-center">No company specified.</h1></div>';
    }
});