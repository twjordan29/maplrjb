document.addEventListener('DOMContentLoaded', () => {
    // Animated counter for stats
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);
        const counter = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(counter);
            } else {
                element.textContent = Math.floor(start).toLocaleString();
            }
        }, 16);
    }

    // Intersection Observer for stats animation
    const stats = document.querySelectorAll('.stat-number');
    if (stats.length > 0) {
        const observerCallback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.dataset.target);
                    animateCounter(entry.target, target);
                    observer.unobserve(entry.target);
                }
            });
        };
        const observer = new IntersectionObserver(observerCallback, { threshold: 0.5 });
        stats.forEach(stat => {
            observer.observe(stat);
        });
    }

    // Search functionality for hero section
    const heroSearchForm = document.querySelector('.hero-section .search-box form');
    if (heroSearchForm) {
        heroSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const jobSearch = document.getElementById('jobSearch').value;
            const locationSearch = document.getElementById('locationSearch').value;
            if (jobSearch.trim() === '') {
                alert('Please enter a job title or keyword');
                return;
            }
            alert(`Searching for "${jobSearch}" in ${locationSearch}...`);
        });
    }

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Add animation to job cards on scroll
    const jobCards = document.querySelectorAll('.job-card');
    if (jobCards.length > 0) {
        const cardObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        entry.target.style.transition = 'all 0.6s ease';
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        jobCards.forEach(card => {
            cardObserver.observe(card);
        });
    }

    // Job Listings Page
    const jobListingsContainer = document.querySelector('.job-listings');
    const jobDetailViewContainer = document.querySelector('.job-detail-view');

    if (jobListingsContainer && jobDetailViewContainer) {
        const jobs = [
            {
                id: 1,
                title: 'Senior Software Developer',
                company: 'TechMaple Inc.',
                location: 'Toronto, ON',
                salary: '$80K - $120K',
                logo: 'TM',
                headerImage: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop',
                description: 'Join our growing team building innovative solutions for Canadian businesses. We are looking for a skilled developer with experience in modern web technologies. Remote work available.',
                responsibilities: [
                    'Design, develop, and maintain web applications.',
                    'Collaborate with cross-functional teams.',
                    'Write clean, scalable, and efficient code.'
                ],
                qualifications: [
                    '5+ years of experience in software development.',
                    'Proficiency in JavaScript, React, and Node.js.',
                    'Strong problem-solving skills.'
                ]
            },
            {
                id: 2,
                title: 'Marketing Manager',
                company: 'Canadian Manufacturing Co.',
                location: 'Vancouver, BC',
                salary: '$65K - $85K',
                logo: 'CM',
                headerImage: 'https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=1974&auto=format&fit=crop',
                description: 'Lead marketing initiatives for Canada\'s premier manufacturing company. Great benefits package.',
                responsibilities: [
                    'Develop and execute marketing campaigns.',
                    'Manage social media presence.',
                    'Analyze market trends and competitor activity.'
                ],
                qualifications: [
                    '3+ years of experience in marketing.',
                    'Strong understanding of digital marketing channels.',
                    'Excellent communication and leadership skills.'
                ]
            },
            {
                id: 3,
                title: 'Financial Analyst',
                company: 'Northern Finance Group',
                location: 'Calgary, AB',
                salary: '$55K - $75K',
                logo: 'NF',
                headerImage: 'https://images.unsplash.com/photo-1554224155-1696413565d3?q=80&w=2070&auto=format&fit=crop',
                description: 'Analyze financial data for Canadian businesses. Full remote position with quarterly team meetings.',
                responsibilities: [
                    'Prepare financial reports and forecasts.',
                    'Analyze financial data and create financial models.',
                    'Provide financial insights to support business decisions.'
                ],
                qualifications: [
                    'Bachelor\'s degree in Finance, Accounting, or a related field.',
                    'Strong analytical and quantitative skills.',
                    'Proficiency in Microsoft Excel and financial modeling.'
                ]
            }
        ];

        function renderJobListings() {
            jobListingsContainer.innerHTML = '';
            jobs.forEach(job => {
                const jobCard = document.createElement('div');
                jobCard.classList.add('job-card-listing');
                jobCard.dataset.jobId = job.id;
                jobCard.innerHTML = `
                    <div class="d-flex">
                        <div class="company-logo-listing">${job.logo}</div>
                        <div class="ms-3">
                            <h5 class="mb-1">${job.title}</h5>
                            <p class="text-muted mb-1">${job.company} &middot; ${job.location}</p>
                            <p class="text-success mb-0">${job.salary}</p>
                        </div>
                    </div>
                `;
                jobListingsContainer.appendChild(jobCard);
            });
        }

        function renderJobDetails(jobId) {
            const job = jobs.find(j => j.id === jobId);
            if (job) {
                jobDetailViewContainer.innerHTML = `
                    <div class="job-detail-header-image" style="background-image: url('${job.headerImage}')"></div>
                    <div class="job-detail-content">
                        <div class="job-detail-header">
                            <div class="d-flex align-items-center">
                                <div class="company-logo-listing-lg">${job.logo}</div>
                                <div class="ms-3">
                                    <h4 class="mb-1">${job.title}</h4>
                                    <p class="text-muted mb-1">${job.company} &middot; ${job.location}</p>
                                    <p class="text-success mb-0">${job.salary}</p>
                                </div>
                            </div>
                            <button class="btn btn-primary mt-3 w-100">Apply Now</button>
                        </div>
                        <div class="job-detail-body">
                            <h5>Job Description</h5>
                            <p>${job.description}</p>
                            <h5>Responsibilities</h5>
                            <ul>
                                ${job.responsibilities.map(r => `<li>${r}</li>`).join('')}
                            </ul>
                            <h5>Qualifications</h5>
                            <ul>
                                ${job.qualifications.map(q => `<li>${q}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `;
            }
        }

        jobListingsContainer.addEventListener('click', (e) => {
            const card = e.target.closest('.job-card-listing');
            if (card) {
                const jobId = parseInt(card.dataset.jobId);
                renderJobDetails(jobId);
                document.querySelectorAll('.job-card-listing').forEach(c => c.classList.remove('active'));
                card.classList.add('active');
            }
        });

        // Initial render
        renderJobListings();
        if (jobs.length > 0) {
            renderJobDetails(jobs[0].id);
            const firstCard = jobListingsContainer.querySelector('.job-card-listing');
            if (firstCard) {
                firstCard.classList.add('active');
            }
        }
    }
});