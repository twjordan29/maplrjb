document.addEventListener('DOMContentLoaded', function() {
    const jobListingsContainer = document.getElementById('job-listings-container');
    const jobDetailContent = document.getElementById('job-detail-content');
    const jobListingsColumn = document.querySelector('.job-listings-column');
    const jobDetailColumn = document.querySelector('.job-detail-column');
    let jobs = [];
    let currentPage = 1;
    let totalPages = 1;
    let currentFilters = {
        keyword: '',
        job_type: '',
        salary_type: '',
        salary: '',
        location: '',
        radius: ''
    };

    function renderJobListings() {
        jobListingsContainer.innerHTML = '';
        if (jobs.length === 0) {
            jobListingsContainer.innerHTML = '<p class="p-3 text-muted">No jobs found at the moment.</p>';
            return;
        }
        jobs.forEach(job => {
            const jobCard = document.createElement('div');
            jobCard.className = 'job-card-listing';
            jobCard.dataset.id = job.id;
            jobCard.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="company-logo-listing me-3">${job.company_logo_html}</div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">${job.title}</h6>
                        <p class="mb-1 text-muted">${job.company} ${job.is_verified ? `<img src="images/maple.png" class="verified-icon" alt="Verified Employer" data-bs-toggle="tooltip" data-bs-placement="top" title="${job.company} is a verified employer">` : ''}</p>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>${job.location}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">${timeAgo(job.posted)}</small>
                        <small class="text-success fw-bold d-block mt-1">${job.type}</small>
                    </div>
                </div>
            `;
            jobListingsContainer.appendChild(jobCard);
        });
    }


    function renderJobDetails(jobId) {
        const job = jobs.find(j => j.id == jobId);
        if (!job) {
            jobDetailContent.innerHTML = `
                <div class="text-center p-5">
                    <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Select a job to see details</h4>
                </div>`;
            return;
        }

        logJobEvent(job.id, 'view');

        jobDetailContent.innerHTML = `
            ${job.header_image ? `<div class="job-detail-header-image" style="background-image: url('${job.header_image}');"></div>` : ''}
            <div class="job-detail-content">
                <div class="d-block d-lg-none mb-3">
                    <button class="btn btn-outline-secondary" id="back-to-list-btn"><i class="fas fa-arrow-left me-2"></i>Back to list</button>
                </div>
                <div class="job-detail-header d-flex align-items-start">
                    <div class="company-logo-listing-lg me-4">${job.company_logo_html}</div>
                    <div class="flex-grow-1">
                        <h3 class="fw-bold">${job.title}</h3>
                        <p class="fs-5 text-muted">${job.is_premium ? `<a href="company.php?id=${job.employer_id}" class="company-profile-link">${job.company}</a>` : job.company} ${job.is_verified ? `<img src="images/maple.png" class="verified-icon" alt="Verified Employer" data-bs-toggle="tooltip" data-bs-placement="top" title="${job.company} is a verified employer">` : ''}</p>
                        <p><i class="fas fa-map-marker-alt me-2"></i>${job.location}</p>
                        ${job.salary ? `<p class="text-success fw-bold"><i class="fas fa-dollar-sign me-2"></i>${job.salary}</p>` : ''}
                    </div>
                    <div class="text-end">
                        <div class="d-flex flex-column flex-lg-row gap-2">
                            ${userType !== 'employer' ? `<a href="apply-now.php?job_id=${job.id}" class="btn btn-primary">Apply Now</a>` : ''}
                            ${userType === 'job_seeker' ? `<a href="#" class="btn btn-outline-primary save-job-btn" data-job-id="${job.id}">${savedJobs.includes(job.id) ? 'Unsave Job' : 'Save Job'}</a>` : ''}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="job-description">
                    <div class="job-description">${job.description}</div>
                </div>
            </div>
        `;

        const backBtn = document.getElementById('back-to-list-btn');
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                jobListingsColumn.classList.remove('hidden');
                jobDetailColumn.classList.remove('visible');
            });
        }
    }

    function initializeTooltips() {
        const tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    jobListingsContainer.addEventListener('click', function(e) {
        const card = e.target.closest('.job-card-listing');
        if (card) {
            const currentlyActive = document.querySelector('.job-card-listing.active');
            if (currentlyActive) {
                currentlyActive.classList.remove('active');
            }
            card.classList.add('active');
            renderJobDetails(card.dataset.id);
            initializeTooltips();

            if (window.innerWidth < 992) {
                jobListingsColumn.classList.add('hidden');
                jobDetailColumn.classList.add('visible');
                jobDetailColumn.scrollTo(0, 0);
            }
        }
    });

    jobDetailContent.addEventListener('click', function(e) {
        if (e.target.classList.contains('save-job-btn')) {
            e.preventDefault();
            const jobId = e.target.dataset.jobId;
            const action = e.target.textContent.trim().toLowerCase() === 'save job' ? 'save' : 'unsave';
            toggleSaveJob(jobId, action, e.target);
        }
    });

    async function logJobEvent(jobId, eventType) {
        try {
            await fetch('api/log_job_event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ job_id: jobId, event_type: eventType })
            });
        } catch (error) {
            console.error('Error logging job event:', error);
        }
    }

    jobDetailContent.addEventListener('click', function(e) {
        if (e.target.matches('.btn-primary') && e.target.textContent === 'Apply Now') {
            const job = jobs.find(j => j.id == e.target.href.split('job_id=')[1]);
            if(job) {
                logJobEvent(job.id, 'click');
            }
        }
    });

    async function toggleSaveJob(jobId, action, button) {
        try {
            const response = await fetch('api/save_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    job_id: jobId,
                    action: action
                })
            });
            const result = await response.json();
            if (result.success) {
                if (action === 'save') {
                    button.textContent = 'Unsave Job';
                    savedJobs.push(parseInt(jobId));
                } else {
                    button.textContent = 'Save Job';
                    const index = savedJobs.indexOf(parseInt(jobId));
                    if (index > -1) {
                        savedJobs.splice(index, 1);
                    }
                }
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error saving job:', error);
            alert('An error occurred. Please try again.');
        }
    }

    function renderPagination() {
        const paginationContainer = document.getElementById('pagination-container');
        if (!paginationContainer) return;

        paginationContainer.innerHTML = '';
        let paginationHtml = '<ul class="pagination justify-content-center">';

        // Previous button
        paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>`;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }

        // Next button
        paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>`;

        paginationHtml += '</ul>';
        paginationContainer.innerHTML = paginationHtml;
    }

    async function fetchJobs(page = 1) {
        const params = new URLSearchParams({
            page: page,
            ...currentFilters
        });

        try {
            const response = await fetch(`api/get_jobs.php?${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            jobs = result.data || [];
            currentPage = result.pagination.page;
            totalPages = result.pagination.totalPages;
            initialRender();
            initializeTooltips();
            renderPagination();
        } catch (error) {
            console.error("Could not fetch jobs:", error);
            jobListingsContainer.innerHTML = '<p class="p-3 text-danger">Error loading jobs. Please try again later.</p>';
        }
    }

    function initialRender() {
        renderJobListings();
        if (window.innerWidth >= 992) {
            if (jobs.length > 0) {
                renderJobDetails(jobs[0].id);
                const firstCard = jobListingsContainer.querySelector('.job-card-listing');
                if(firstCard) {
                    firstCard.classList.add('active');
                }
            } else {
                 renderJobDetails(null);
            }
        } else {
            jobDetailColumn.classList.remove('visible');
            jobListingsColumn.classList.remove('hidden');
            renderJobDetails(null);
        }
    }

    function updateSalaryRangeOptions(salaryType) {
        const salaryRangeDropdownMenu = document.getElementById('salaryRangeDropdown').nextElementSibling;
        const salaryRangeButton = document.getElementById('salaryRangeDropdown');
        let optionsHtml = '<li><a class="dropdown-item" href="#" data-filter="salary" data-value="">Any Range</a></li>';

        if (salaryType === 'hourly') {
            salaryRangeButton.textContent = 'Hourly Wage';
            optionsHtml += `
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="41600">> $20/hr</a></li>
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="62400">> $30/hr</a></li>
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="83200">> $40/hr</a></li>
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="104000">> $50/hr</a></li>
            `;
        } else { // Default to annual salary
            salaryRangeButton.textContent = 'Salary Range';
            optionsHtml += `
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="40000">> $40,000</a></li>
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="60000">> $60,000</a></li>
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="80000">> $80,000</a></li>
                <li><a class="dropdown-item" href="#" data-filter="salary" data-value="100000">> $100,000</a></li>
            `;
        }
        salaryRangeDropdownMenu.innerHTML = optionsHtml;
    }

    document.addEventListener('click', function(e) {
        if (e.target.matches('#pagination-container a')) {
            e.preventDefault();
            const page = parseInt(e.target.dataset.page);
            if (page && page !== currentPage) {
                fetchJobs(page);
            }
            return; // Prevent dropdown logic from running
        }

        if (e.target.matches('.dropdown-item')) {
            e.preventDefault();
            const filter = e.target.dataset.filter;
            const value = e.target.dataset.value;
            currentFilters[filter] = value;

            if (filter === 'salary_type') {
                updateSalaryRangeOptions(value);
                // Reset salary range filter when type changes
                currentFilters.salary = '';
            }

            fetchJobs(1); // Reset to page 1 when a filter changes

            // Update dropdown button text
            const dropdownButton = e.target.closest('.dropdown').querySelector('.dropdown-toggle');
            if (value) {
                dropdownButton.textContent = e.target.textContent;
            } else {
                // Reset to default text if "All" or "Any" is selected
                if (filter === 'job_type') dropdownButton.textContent = 'Job Type';
                if (filter === 'salary_type') {
                    dropdownButton.textContent = 'Salary';
                    updateSalaryRangeOptions(''); // Reset salary range to default
                }
                if (filter === 'salary') {
                    const salaryType = currentFilters.salary_type;
                    if (salaryType === 'hourly') {
                        dropdownButton.textContent = 'Hourly Wage';
                    } else {
                        dropdownButton.textContent = 'Salary Range';
                    }
                }
            }
        }
    });

    document.querySelector('.job-filters-bar button[type="submit"]').addEventListener('click', function(e) {
        e.preventDefault();
        const location = document.getElementById('location-search').value;
        const keyword = document.getElementById('keyword-search').value;
        currentFilters.location = location;
        currentFilters.keyword = keyword;
        fetchJobs(1);
    });

const keywordSearch = document.getElementById('keyword-search');
   const suggestionsContainer = document.getElementById('suggestions-container');

   keywordSearch.addEventListener('input', async function() {
       const term = keywordSearch.value;
       if (term.length < 2) {
           suggestionsContainer.innerHTML = '';
           suggestionsContainer.style.display = 'none';
           return;
       }

       const response = await fetch(`api/get_search_suggestions.php?term=${term}`);
       const suggestions = await response.json();

       if (suggestions.length > 0) {
           suggestionsContainer.style.display = 'block';
           suggestionsContainer.innerHTML = suggestions.map(s => `
               <div class="suggestion-item" data-value="${s.value}">
                   ${s.value} <span class="suggestion-type">(${s.type})</span>
               </div>
           `).join('');
       } else {
           suggestionsContainer.style.display = 'none';
       }
   });

   suggestionsContainer.addEventListener('click', function(e) {
       if (e.target.classList.contains('suggestion-item')) {
           const value = e.target.dataset.value;
           keywordSearch.value = value;
           suggestionsContainer.innerHTML = '';
           suggestionsContainer.style.display = 'none';
           currentFilters.keyword = value;
           fetchJobs(1);
       }
   });

   document.addEventListener('click', function(e) {
       if (!keywordSearch.contains(e.target) && !suggestionsContainer.contains(e.target)) {
           suggestionsContainer.style.display = 'none';
       }
   });

    fetchJobs();
    window.addEventListener('resize', initialRender);
});