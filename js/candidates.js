document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    const candidatesList = document.getElementById('candidates-list');
    const paginationContainer = document.getElementById('candidates-pagination');

    function fetchCandidates(page = 1) {
        const keywords = document.getElementById('keywords').value;
        const location = document.getElementById('location').value;
        const experience = document.getElementById('experience').value;
        const availability = Array.from(document.getElementById('availability').selectedOptions).map(o => o.value);
        const salary = document.getElementById('salary').value;
        const skills = Array.from(document.getElementById('skills').selectedOptions).map(o => o.value);

        const params = new URLSearchParams({
            page,
            keywords,
            location,
            experience_years: experience,
            desired_salary_min: salary
        });

        availability.forEach(a => params.append('availability[]', a));
        skills.forEach(s => params.append('skills[]', s));

        fetch(`api/get_candidates.php?${params}`)
            .then(response => response.json())
            .then(data => {
                const { candidates, total_pages, current_page } = data;
                renderCandidates(candidates);
                renderPagination(total_pages, current_page);
            });
    }

    function renderCandidates(candidates) {
        if (candidates.length === 0) {
            candidatesList.innerHTML = '<p>No candidates found.</p>';
            return;
        }

        let html = '';
        candidates.forEach(candidate => {
            html += `
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="text-center">
                                <div class="profile-avatar mx-auto">
                                    ${candidate.profile_picture
                                        ? `<img src="${candidate.profile_picture}" alt="${candidate.first_name}">`
                                        : `<span>${(candidate.first_name || '').charAt(0)}${(candidate.last_name || '').charAt(0)}</span>`
                                    }
                                </div>
                            </div>
                            <div class="text-center">
                                <h5 class="card-title">${candidate.first_name} ${candidate.last_name}</h5>
                                <div class="card-text mt-3 mb-3">${candidate.headline}</div>
                                <p class="text-muted">${candidate.location_city}, ${candidate.location_province}</p>
                                <div class="d-flex flex-wrap justify-content-center mt-2">
                                    ${candidate.skills.slice(0, 2).map(skill => `<span class="badge bg-secondary me-1 mb-1">${skill}</span>`).join('')}
                                    ${candidate.skills.length > 2 ? `<span class="badge bg-secondary me-1 mb-1">...</span>` : ''}
                                </div>
                            </div>
                            <div class="mt-auto text-center">
                                <a href="candidate-profile.php?id=${candidate.user_id}" class="btn btn-sm btn-primary">View Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        candidatesList.innerHTML = html;
    }

    function renderPagination(totalPages, currentPage) {
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

        document.querySelectorAll('#candidates-pagination a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const page = this.dataset.page;
                fetchCandidates(page);
            });
        });
    }

    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchCandidates();
    });

    new TomSelect("#availability", {
        plugins: ['remove_button'],
        create: false,
    });

    fetch('api/get_skills.php')
        .then(response => response.json())
        .then(data => {
            new TomSelect("#skills", {
                plugins: ['remove_button'],
                create: false,
                valueField: 'id',
                labelField: 'name',
                searchField: ['name'],
                options: data,
                placeholder: 'Start typing to find a skill...'
            });
        })
        .catch(error => console.error('Error fetching skills:', error));

    fetchCandidates();
});