document.addEventListener('DOMContentLoaded', function() {
    const interviewsContainer = document.getElementById('interviews-container');

    function fetchInterviews() {
        fetch('api/get_interviews.php')
            .then(response => response.json())
            .then(interviews => {
                if (interviews.length > 0) {
                    let interviewsHtml = '<div class="list-group">';
                    interviews.forEach(interview => {
                        interviewsHtml += `
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">Interview for ${interview.job_title}</h5>
                                    <small>${new Date(interview.interview_datetime).toLocaleString()}</small>
                                </div>
                                <p class="mb-1">With: ${interview.company_name}</p>
                                <p class="mb-1">Status: <span class="badge bg-info">${interview.status}</span></p>
                                ${interview.notes ? `<p class="mb-1">Notes: ${interview.notes}</p>` : ''}
                                ${interview.status === 'pending' ? `
                                    <button class="btn btn-sm btn-success confirm-interview-btn" data-interview-id="${interview.interview_id}">Confirm</button>
                                    <button class="btn btn-sm btn-danger decline-interview-btn" data-interview-id="${interview.interview_id}">Decline</button>
                                ` : ''}
                            </div>
                        `;
                    });
                    interviewsHtml += '</div>';
                    interviewsContainer.innerHTML = interviewsHtml;
                } else {
                    interviewsContainer.innerHTML = '<p>You have no interviews scheduled.</p>';
                }
            });
    }

    interviewsContainer.addEventListener('click', function(e) {
        const target = e.target;
        if (target.classList.contains('confirm-interview-btn') || target.classList.contains('decline-interview-btn')) {
            const interviewId = target.dataset.interviewId;
            const status = target.classList.contains('confirm-interview-btn') ? 'confirmed' : 'declined';

            fetch('api/update_interview_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ interview_id: interviewId, status: status })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    fetchInterviews(); // Refresh the list
                } else {
                    // Optionally, show an error message
                }
            });
        }
    });

    fetchInterviews();
});