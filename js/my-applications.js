document.addEventListener('DOMContentLoaded', function() {
    const applicationsContainer = document.getElementById('applications-container');

    fetch('api/get_my_applications.php')
        .then(response => response.json())
        .then(applications => {
            if (applications.length > 0) {
                let applicationsHtml = '<div class="list-group">';
                applications.forEach(app => {
                    let statusHtml = '';
                    switch (app.status.toLowerCase()) {
                        case 'new':
                        case 'under-review':
                            statusHtml = '<span class="badge bg-primary">In Progress</span>';
                            break;
                        case 'not-suitable':
                            statusHtml = '<span class="badge bg-danger">Rejected</span>';
                            break;
                        case 'interview':
                            const interviewDate = new Date(app.interview_date);
                            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                            statusHtml = app.interview_date ? `<span class="badge bg-success">Interview Scheduled: ${interviewDate.toLocaleDateString('en-US', options)}</span>` : '<span class="badge bg-primary">In Progress</span>';
                            break;
                        case 'hired':
                            statusHtml = '<span class="badge bg-success">Hired</span>';
                            break;
                        default:
                            statusHtml = '<span class="badge bg-secondary">Unknown</span>';
                    }

                    applicationsHtml += `
                        <div class="application-list-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">${app.job_title}</h5>
                                <small>Applied on: ${new Date(app.application_date).toLocaleDateString()}</small>
                            </div>
                            <p class="mb-1">${app.company_name}</p>
                            <div>${statusHtml}</div>
                        </div>
                    `;
                });
                applicationsHtml += '</div>';
                applicationsContainer.innerHTML = applicationsHtml;
            } else {
                applicationsContainer.innerHTML = '<p>You have not submitted any applications yet.</p>';
            }
        });
});