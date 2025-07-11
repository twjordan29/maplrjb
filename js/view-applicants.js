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
        buttons += ` <button class="btn btn-sm btn-info view-cover-letter-btn" data-cover-letter="${escape(applicant.cover_letter)}">View Cover Letter</button>`;

        if (applicant.is_rejected) {
            return '';
        }

        if (statusId === 'interview') {
            if (applicant.interview_date) {
                buttons += ` <button class="btn btn-sm btn-secondary" disabled>Interview Scheduled</button>`;
            } else {
                buttons += ` <button class="btn btn-sm btn-success schedule-interview-btn" data-applicant-id="${applicant.user_id}" data-application-id="${applicant.application_id}">Schedule Interview</button>`;
            }
        } else if (statusId === 'not-suitable') {
            buttons += ` <button class="btn btn-sm btn-warning send-email-btn" data-applicant-id="${applicant.user_id}" data-application-id="${applicant.application_id}">Send Email</button>`;
        }

        if (applicant.screening_answers && applicant.screening_answers.length > 0) {
            buttons += ` <button class="btn btn-sm btn-secondary view-screening-answers-btn">View Screening Answers</button>`;
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
                if (applicant.is_rejected) {
                    card.classList.add('rejected');
                }
                card.dataset.applicationId = applicant.application_id;
                card.dataset.applicantId = applicant.user_id;
                card.dataset.coverLetter = applicant.cover_letter;
                if (applicant.screening_answers) {
                    card.dataset.screeningAnswers = JSON.stringify(applicant.screening_answers);
                }
                card.innerHTML = `
                    <h6>${applicant.first_name} ${applicant.last_name}</h6>
                    <p class="small">${applicant.email}</p>
                    <p class="small">${applicant.city}, ${applicant.province}</p>
                    <div class="mt-2">${getCardButtons(applicant)}</div>
                    ${applicant.is_rejected ? '<div class="rejected-overlay"><div class="rejected-text">This applicant has been notified of their rejection.</div></div>' : ''}
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
                group: {
                    name: 'applicants',
                    pull: (to, from, dragEl) => {
                        return !dragEl.classList.contains('rejected');
                    },
                    put: (to, from, dragEl) => {
                        return !dragEl.classList.contains('rejected');
                    }
                },
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

        if (e.target.classList.contains('view-cover-letter-btn')) {
            const button = e.target;
            const coverLetter = unescape(button.dataset.coverLetter);
            const coverLetterModal = new bootstrap.Modal(document.getElementById('coverLetterModal'));
            document.getElementById('coverLetterContent').innerText = coverLetter;
            coverLetterModal.show();
        }

        if (e.target.classList.contains('view-screening-answers-btn')) {
            const card = e.target.closest('.kanban-card');
            const answers = JSON.parse(card.dataset.screeningAnswers);
            const answersModal = new bootstrap.Modal(document.getElementById('screeningAnswersModal'));
            const answersContent = document.getElementById('screeningAnswersContent');
            
            answersContent.innerHTML = '';
            if (answers && answers.length > 0) {
                answers.forEach(answer => {
                    answersContent.innerHTML += `
                        <div class="mb-3">
                            <strong>${answer.question_text}</strong>
                            <p>${answer.answer_text}</p>
                        </div>
                    `;
                });
            } else {
                answersContent.innerHTML = '<p>No screening answers found.</p>';
            }
            
            answersModal.show();
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
    const profanityList = ['fuck', 'shit', 'balls', 'damn', 'hell', 'bitch', 'asshole', 'cunt', 'dick', 'pussy', 'motherfucker']; // Add more words as needed
    const emailBodyInput = document.getElementById('email_body');
    const emailSubjectInput = document.getElementById('email_subject');
    const profanityWarningBody = document.getElementById('profanity-warning-body');
    const profanityWarningSubject = document.getElementById('profanity-warning-subject');

    function checkProfanity(text) {
        for (const word of profanityList) {
            const regex = new RegExp(`\\b${word}\\b`, 'gi');
            if (regex.test(text)) {
                return true;
            }
        }
        return false;
    }

    function validateEmailFields() {
        const subjectHasProfanity = checkProfanity(emailSubjectInput.value);
        const bodyHasProfanity = checkProfanity(emailBodyInput.value);

        profanityWarningSubject.style.display = subjectHasProfanity ? 'block' : 'none';
        profanityWarningBody.style.display = bodyHasProfanity ? 'block' : 'none';

        return !subjectHasProfanity && !bodyHasProfanity;
    }

    emailBodyInput.addEventListener('input', validateEmailFields);
    emailSubjectInput.addEventListener('input', validateEmailFields);

    sendEmailForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateEmailFields()) {
            alert('Inappropriate language detected. Please revise your email.');
            return;
        }

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
                fetchAndRenderApplicants();
            }
        });
    });

    const emailTemplateSelect = document.getElementById('email_template');
    emailTemplateSelect.addEventListener('change', function() {
        const template = this.value;
        const subjectInput = document.getElementById('email_subject');
        const bodyInput = document.getElementById('email_body');

        switch (template) {
            case 'rejection_1':
                subjectInput.value = 'Update on your application';
                bodyInput.value = 'Dear Applicant,\n\nThank you for your interest in the position. We have decided to move forward with other candidates whose qualifications better meet our needs at this time.\n\nWe wish you the best in your job search.\n\nSincerely,\nThe Hiring Team';
                break;
            case 'rejection_2':
                subjectInput.value = 'Regarding your recent application';
                bodyInput.value = 'Hello,\n\nThank you for applying. While your experience is impressive, we have chosen to proceed with a candidate whose skills are a closer match for this particular role.\n\nBest regards,\nThe Hiring Team';
                break;
            case 'rejection_3':
                subjectInput.value = 'Your application status';
                bodyInput.value = 'Hi there,\n\nWe appreciate you taking the time to apply. We received a high volume of applications, and have decided not to move forward with your candidacy at this time.\n\nThanks again,\nThe Hiring Team';
                break;
            default:
                subjectInput.value = '';
                bodyInput.value = '';
        }
    });
});