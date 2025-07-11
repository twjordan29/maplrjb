document.addEventListener('DOMContentLoaded', function () {
    const kanbanBoard = document.getElementById('kanban-board');
    const jobId = new URLSearchParams(window.location.search).get('job_id');

    const columns = {
        'New Applicants': 'new',
        'Under Review': 'under-review',
        'Interview': 'interview',
        'Not Suitable': 'not-suitable',
        'Hired': 'hired'
    };

    function renderBoard() {
        kanbanBoard.innerHTML = '';
        for (const [title, id] of Object.entries(columns)) {
            kanbanBoard.innerHTML += `
                <div class="kanban-column">
                    <h5 class="kanban-column-title">${title}</h5>
                    <div class="kanban-cards" id="kanban-column-${id}" data-status="${id}"></div>
                </div>
            `;
        }
    }

    function getCardButtons(applicant) {
        const statusId = applicant.status ? applicant.status.toLowerCase().replace(/ /g, '-') : 'new';
        let buttons = `<a href="${applicant.resume_path}" target="_blank" class="btn btn-sm btn-outline-primary">View Resume</a>`;

        if (statusId === 'interview') {
            buttons += ` <button class="btn btn-sm btn-success schedule-interview-btn" data-applicant-id="${applicant.user_id}" data-application-id="${applicant.application_id}">Schedule Interview</button>`;
        } else if (statusId === 'not-suitable') {
            buttons += ` <button class="btn btn-sm btn-warning send-email-btn" data-applicant-id="${applicant.user_id}" data-application-id="${applicant.application_id}">Send Email</button>`;
        }
        return buttons;
    }

    function renderCards(applicants) {
        document.querySelectorAll('.kanban-cards').forEach(column => column.innerHTML = '');

        applicants.forEach(applicant => {
            const statusId = applicant.status ? applicant.status.toLowerCase().replace(/ /g, '-') : 'new';
            const column = document.getElementById(`kanban-column-${statusId}`);
            if (column) {
                const card = document.createElement('div');
                card.className = 'kanban-card';
                card.dataset.applicationId = applicant.application_id;
                card.dataset.applicantId = applicant.user_id;
                card.innerHTML = `
                    <h6>${applicant.first_name} ${applicant.last_name}</h6>
                    <p class="small">${applicant.email}</p>
                    <div class="mt-2">${getCardButtons(applicant)}</div>
                `;
                column.appendChild(card);
            }
        });
    }
    
    function fetchAndRenderApplicants() {
        fetch(`api/get_applicants.php?job_id=${jobId}`)
            .then(response => response.json())
            .then(applicants => {
                renderCards(applicants);
                setupDragAndDrop();
            });
    }

    function setupDragAndDrop() {
        const cardColumns = document.querySelectorAll('.kanban-cards');
        cardColumns.forEach(column => {
            new Sortable(column, {
                group: 'applicants',
                animation: 150,
                onEnd: function (evt) {
                    const card = evt.item;
                    const newStatus = evt.to.dataset.status;
                    const applicationId = card.dataset.applicationId;
                    updateApplicantStatus(applicationId, newStatus);
                }
            });
        });
    }

    function updateApplicantStatus(applicationId, status) {
        fetch('api/update_interview_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ application_id: applicationId, status: status })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                fetchAndRenderApplicants(); // Re-render cards with new buttons
            } else {
                console.error('Failed to update status');
            }
        });
    }

    if (jobId) {
        renderBoard();
        fetchAndRenderApplicants();
    }

    // Event delegation for dynamically created buttons
    kanbanBoard.addEventListener('click', function(e) {
        if (e.target.classList.contains('schedule-interview-btn')) {
            const button = e.target;
            const applicationId = button.dataset.applicationId;
            const applicantId = button.dataset.applicantId;
            // Logic to open schedule interview modal
            const scheduleModal = new bootstrap.Modal(document.getElementById('scheduleInterviewModal'));
            document.getElementById('applicationId').value = applicationId;
            document.getElementById('applicantId').value = applicantId;
            scheduleModal.show();
        }

        if (e.target.classList.contains('send-email-btn')) {
            const button = e.target;
            const applicationId = button.dataset.applicationId;
            const applicantId = button.dataset.applicantId;
            // Logic to open send email modal
            const emailModal = new bootstrap.Modal(document.getElementById('sendEmailModal'));
            document.getElementById('emailApplicationId').value = applicationId;
            emailModal.show();
        }
    });

    const scheduleForm = document.getElementById('scheduleInterviewForm');
    scheduleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(scheduleForm);
        const data = Object.fromEntries(formData.entries());

        fetch('api/schedule_interview.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const scheduleModal = bootstrap.Modal.getInstance(document.getElementById('scheduleInterviewModal'));
                scheduleModal.hide();
            }
        });
    });

    const sendEmailForm = document.getElementById('sendEmailForm');
    sendEmailForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(sendEmailForm);
        const data = Object.fromEntries(formData.entries());

        fetch('api/send_rejection_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const emailModal = bootstrap.Modal.getInstance(document.getElementById('sendEmailModal'));
                emailModal.hide();
            }
        });
    });

    const emailTemplateSelect = document.getElementById('email_template');
    emailTemplateSelect.addEventListener('change', function() {
        const template = this.value;
        const subjectInput = document.getElementById('email_subject');
        const bodyInput = document.getElementById('email_body');

        if (template === 'rejection_1') {
            subjectInput.value = 'Update on your application';
            bodyInput.value = 'Dear Applicant,\n\nThank you for your interest in the position. We have decided to move forward with other candidates whose qualifications better meet our needs at this time.\n\nWe wish you the best in your job search.\n\nSincerely,\nThe Hiring Team';
        } else {
            subjectInput.value = '';
            bodyInput.value = '';
        }
    });
});