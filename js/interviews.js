document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    fetch('api/get_employer_interviews.php')
        .then(response => response.json())
        .then(interviews => {
            const events = interviews.map(interview => ({
                title: `${interview.job_title} with ${interview.applicant_name}`,
                start: interview.interview_datetime,
                extendedProps: {
                    status: interview.status
                }
            }));

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
                eventDidMount: function(info) {
                    let badgeClass = 'bg-secondary';
                    switch (info.event.extendedProps.status.toLowerCase()) {
                        case 'confirmed':
                            badgeClass = 'bg-success';
                            break;
                        case 'pending':
                            badgeClass = 'bg-warning text-dark';
                            break;
                        case 'declined':
                            badgeClass = 'bg-danger';
                            break;
                    }
                    const statusHtml = `<span class="badge ${badgeClass}">${info.event.extendedProps.status}</span>`;
                    info.el.querySelector('.fc-event-title').innerHTML += `<div class="mt-1">${statusHtml}</div>`;
                }
            });

            calendar.render();
        });
});